# Plan: Donation Boxes

## Summary

Implement a donation campaign system allowing users to create "donation boxes" with specific or open-ended amounts. Each box has a purpose/reason, visibility settings (public/unlisted/private), and status (open/closed). Amounts are stored in RON (Romanian Leu). Following the existing Service pattern, we'll create a complete CRUD implementation with Inertia.js Vue pages.

## User Story

As a user
I want to create donation boxes with customizable amounts and visibility
So that I can raise money for various causes like emergencies or charitable purposes

## Metadata

| Field | Value |
|-------|-------|
| Type | NEW_CAPABILITY |
| Complexity | MEDIUM |
| Systems Affected | Models, Services, Controllers, Routes, Vue Pages, Database |

---

## Patterns to Follow

### Controller Pattern
```php
// SOURCE: app/Http/Controllers/Settings/ProfileController.php:31-42
public function update(ProfileUpdateRequest $request): RedirectResponse
{
    $request->user()->fill($request->validated());

    if ($request->user()->isDirty('email')) {
        $request->user()->email_verified_at = null;
    }

    $request->user()->save();

    return to_route('profile.edit');
}
```

### Form Request Pattern
```php
// SOURCE: app/Concerns/ProfileValidationRules.php:28-31
protected function nameRules(): array
{
    return ['required', 'string', 'max:255'];
}
```

### Model Pattern
```php
// SOURCE: app/Models/User.php:44-51
protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
```

### Migration Pattern
```php
// SOURCE: database/migrations/0001_01_01_000000_create_users_table.php:14-21
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->timestamps();
});
```

### Test Pattern
```php
// SOURCE: tests/Feature/Settings/ProfileUpdateTest.php:17-36
test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->name)->toBe('Test User');
    expect($user->email)->toBe('test@example.com');
});
```

### Vue Page Pattern
```vue
// SOURCE: resources/js/pages/settings/Profile.vue:1-31
<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3'
import HeadingSmall from '@/components/HeadingSmall.vue'
import { type BreadcrumbItem, type SharedData, type User } from '@/types'

interface Props {
    mustVerifyEmail: boolean
    status?: string
}

defineProps<Props>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Profile settings', href: route('profile.edit') },
]

const page = usePage<SharedData>()
const user = page.props.auth.user as User
</script>
```

---

## Files to Change

| File | Action | Purpose |
|------|--------|---------|
| `app/Enums/DonationBoxVisibility.php` | CREATE | Visibility enum (Public, Unlisted, Private) |
| `app/Enums/DonationBoxStatus.php` | CREATE | Status enum (Open, Closed) |
| `app/Models/DonationBox.php` | CREATE | Eloquent model with relationships |
| `app/Services/DonationBoxService.php` | CREATE | Business logic for CRUD operations |
| `app/Http/Controllers/DonationBoxController.php` | CREATE | Resource controller |
| `app/Http/Requests/StoreDonationBoxRequest.php` | CREATE | Create validation rules |
| `app/Http/Requests/UpdateDonationBoxRequest.php` | CREATE | Update validation rules |
| `database/migrations/xxxx_create_donation_boxes_table.php` | CREATE | Database schema |
| `database/factories/DonationBoxFactory.php` | CREATE | Test factory |
| `resources/js/pages/donation-boxes/Index.vue` | CREATE | List all user's donation boxes |
| `resources/js/pages/donation-boxes/Create.vue` | CREATE | Create form |
| `resources/js/pages/donation-boxes/Show.vue` | CREATE | View single donation box |
| `resources/js/pages/donation-boxes/Edit.vue` | CREATE | Edit form |
| `resources/js/types/index.ts` | UPDATE | Add DonationBox type |
| `routes/web.php` | UPDATE | Add resource routes |
| `app/Providers/AppServiceProvider.php` | UPDATE | Register service singleton |
| `tests/Feature/DonationBox/DonationBoxTest.php` | CREATE | Feature tests |

---

## Tasks

Execute in order. Each task is atomic and verifiable.

### Task 1: Create Enums

- **File**: `app/Enums/DonationBoxVisibility.php`
- **Action**: CREATE
- **Implement**: Backed string enum with cases: `Public = 'public'`, `Unlisted = 'unlisted'`, `Private = 'private'`
- **Validate**: `php artisan tinker --execute="App\Enums\DonationBoxVisibility::cases()"`

- **File**: `app/Enums/DonationBoxStatus.php`
- **Action**: CREATE
- **Implement**: Backed string enum with cases: `Open = 'open'`, `Closed = 'closed'`
- **Validate**: `php artisan tinker --execute="App\Enums\DonationBoxStatus::cases()"`

### Task 2: Create Migration

- **File**: `database/migrations/xxxx_create_donation_boxes_table.php`
- **Action**: CREATE (use `php artisan make:migration create_donation_boxes_table --no-interaction`)
- **Implement**:
  ```php
  Schema::create('donation_boxes', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->string('title');
      $table->text('purpose');
      $table->decimal('target_amount', 12, 2)->nullable(); // null = open-ended
      $table->decimal('current_amount', 12, 2)->default(0);
      $table->string('currency', 3)->default('RON');
      $table->string('visibility')->default('public'); // public, unlisted, private
      $table->string('status')->default('open'); // open, closed
      $table->timestamps();
  });
  ```
- **Mirror**: `database/migrations/0001_01_01_000000_create_users_table.php:14-22`
- **Validate**: `php artisan migrate`

### Task 3: Create Model

- **File**: `app/Models/DonationBox.php`
- **Action**: CREATE (use `php artisan make:model DonationBox --no-interaction`)
- **Implement**:
  - `$fillable`: title, purpose, target_amount, current_amount, currency, visibility, status
  - `casts()` method: visibility → DonationBoxVisibility, status → DonationBoxStatus, target_amount → decimal:2, current_amount → decimal:2
  - `user()` relationship: belongsTo User
  - Scopes: `scopePublic()`, `scopeOpen()`, `scopeForUser()`
- **Mirror**: `app/Models/User.php:11-52`
- **Validate**: `php artisan tinker --execute="new App\Models\DonationBox"`

### Task 4: Create Factory

- **File**: `database/factories/DonationBoxFactory.php`
- **Action**: CREATE (use `php artisan make:factory DonationBoxFactory --no-interaction`)
- **Implement**:
  - `definition()`: title, purpose (fake sentences), target_amount (nullable random), visibility, status
  - `openEnded()` state: target_amount = null
  - `closed()` state: status = closed
  - `private()` state: visibility = private
- **Mirror**: `database/factories/UserFactory.php:24-58`
- **Validate**: `php artisan tinker --execute="App\Models\DonationBox::factory()->make()"`

### Task 5: Create Service

- **File**: `app/Services/DonationBoxService.php`
- **Action**: CREATE (use `php artisan make:class Services/DonationBoxService --no-interaction`)
- **Implement**:
  ```php
  class DonationBoxService
  {
      public function create(User $user, array $data): DonationBox
      public function update(DonationBox $donationBox, array $data): DonationBox
      public function delete(DonationBox $donationBox): bool
      public function listForUser(User $user): Collection
      public function findPublic(int $id): ?DonationBox
  }
  ```
- **Mirror**: CLAUDE.md Service Structure example
- **Validate**: `composer test:lint`

### Task 6: Create Form Requests

- **File**: `app/Http/Requests/StoreDonationBoxRequest.php`
- **Action**: CREATE (use `php artisan make:request StoreDonationBoxRequest --no-interaction`)
- **Implement**:
  - `authorize()`: return true (any authenticated user)
  - `rules()`: title (required, string, max:255), purpose (required, string, max:5000), target_amount (nullable, numeric, min:1), visibility (required, enum), status (required, enum)
- **Mirror**: `app/Http/Requests/Settings/ProfileUpdateRequest.php:9-22`
- **Validate**: `composer test:lint`

- **File**: `app/Http/Requests/UpdateDonationBoxRequest.php`
- **Action**: CREATE (use `php artisan make:request UpdateDonationBoxRequest --no-interaction`)
- **Implement**: Same rules as Store, but all fields optional for partial updates
- **Validate**: `composer test:lint`

### Task 7: Create Controller

- **File**: `app/Http/Controllers/DonationBoxController.php`
- **Action**: CREATE (use `php artisan make:controller DonationBoxController --no-interaction`)
- **Implement**:
  ```php
  public function __construct(private DonationBoxService $service) {}

  public function index(Request $request): Response  // List user's boxes
  public function create(): Response                  // Show create form
  public function store(StoreDonationBoxRequest $request): RedirectResponse
  public function show(DonationBox $donationBox): Response
  public function edit(DonationBox $donationBox): Response
  public function update(UpdateDonationBoxRequest $request, DonationBox $donationBox): RedirectResponse
  public function destroy(DonationBox $donationBox): RedirectResponse
  ```
- **Mirror**: `app/Http/Controllers/Settings/ProfileController.php:15-60`
- **Validate**: `composer test:lint`

### Task 8: Add Routes

- **File**: `routes/web.php`
- **Action**: UPDATE
- **Implement**: Add resource route inside auth middleware group
  ```php
  Route::middleware(['auth', 'verified'])->group(function () {
      Route::resource('donation-boxes', DonationBoxController::class);
  });
  ```
- **Mirror**: `routes/settings.php:9-14`
- **Validate**: `php artisan route:list --path=donation`

### Task 9: Register Service

- **File**: `app/Providers/AppServiceProvider.php`
- **Action**: UPDATE
- **Implement**: Add `$this->app->singleton(DonationBoxService::class)` in `register()`
- **Mirror**: CLAUDE.md Registering Services example
- **Validate**: `php artisan tinker --execute="app(App\Services\DonationBoxService::class)"`

### Task 10: Add TypeScript Types

- **File**: `resources/js/types/index.ts`
- **Action**: UPDATE
- **Implement**:
  ```typescript
  export type DonationBoxVisibility = 'public' | 'unlisted' | 'private'
  export type DonationBoxStatus = 'open' | 'closed'

  export interface DonationBox {
      id: number
      user_id: number
      title: string
      purpose: string
      target_amount: number | null
      current_amount: number
      currency: string
      visibility: DonationBoxVisibility
      status: DonationBoxStatus
      created_at: string
      updated_at: string
      user?: User
  }
  ```
- **Validate**: `npm run lint`

### Task 11: Create Vue Pages

- **File**: `resources/js/pages/donation-boxes/Index.vue`
- **Action**: CREATE
- **Implement**: List page showing user's donation boxes with links to create/edit/view
- **Mirror**: `resources/js/pages/settings/Profile.vue:1-50`
- **Validate**: `npm run lint`

- **File**: `resources/js/pages/donation-boxes/Create.vue`
- **Action**: CREATE
- **Implement**: Form with title, purpose, target_amount (optional), visibility select, status select
- **Mirror**: `resources/js/pages/settings/Profile.vue:48-124` (form pattern)
- **Validate**: `npm run lint`

- **File**: `resources/js/pages/donation-boxes/Show.vue`
- **Action**: CREATE
- **Implement**: Display donation box details, progress bar if target set, edit/delete buttons for owner
- **Validate**: `npm run lint`

- **File**: `resources/js/pages/donation-boxes/Edit.vue`
- **Action**: CREATE
- **Implement**: Edit form mirroring Create.vue structure
- **Mirror**: `resources/js/pages/donation-boxes/Create.vue`
- **Validate**: `npm run lint`

### Task 12: Create Feature Tests

- **File**: `tests/Feature/DonationBox/DonationBoxTest.php`
- **Action**: CREATE (use `php artisan make:test DonationBox/DonationBoxTest --pest --no-interaction`)
- **Implement**:
  ```php
  uses(RefreshDatabase::class);

  test('guests cannot access donation boxes')
  test('users can view their donation boxes')
  test('users can create a donation box')
  test('users can create an open-ended donation box')
  test('users can update their donation box')
  test('users cannot update another users donation box')
  test('users can delete their donation box')
  test('users can close a donation box')
  ```
- **Mirror**: `tests/Feature/Settings/ProfileUpdateTest.php:5-87`
- **Validate**: `php artisan test --filter=DonationBox`

---

## Validation

```bash
# Lint PHP
composer test:lint

# Run all tests
composer test

# Frontend lint
npm run lint

# Build frontend
npm run build
```

---

## Acceptance Criteria

- [ ] All tasks completed
- [ ] Migration runs successfully
- [ ] Model with enums works correctly
- [ ] Service handles CRUD operations
- [ ] Controller uses Form Requests
- [ ] Routes registered and accessible
- [ ] Vue pages render correctly
- [ ] All tests pass
- [ ] Lint checks pass
- [ ] Follows existing codebase patterns
