# Donation Boxes v2 - Stripe Payments Implementation Plan

## Overview

Add Stripe payment processing to existing donation boxes feature, enabling:
- **Guest donations** (no account required)
- **Anonymous option** (donors can hide their name)
- **Manual withdrawals** (funds accumulate, owner requests withdrawal)

---

## 1. Dependencies to Install

```bash
composer require stripe/stripe-php
npm install @stripe/stripe-js
```

---

## 2. Environment Variables

Add to `.env.example`:
```
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

Add to `config/services.php`:
```php
'stripe' => [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
],
```

---

## 3. Database Schema

### 3.1 Add withdrawable_balance to users

**Migration:** `add_withdrawable_balance_to_users_table`
```php
$table->decimal('withdrawable_balance', 12, 2)->default(0);
```

### 3.2 Create donations table

**Migration:** `create_donations_table`
```php
Schema::create('donations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('donation_box_id')->constrained()->cascadeOnDelete();
    $table->string('donor_name');
    $table->string('donor_email');
    $table->text('message')->nullable();
    $table->boolean('is_anonymous')->default(false);
    $table->decimal('amount', 12, 2);
    $table->string('currency', 3)->default('RON');
    $table->string('status')->default('pending'); // pending, completed, failed, refunded
    $table->string('stripe_payment_intent_id')->nullable()->unique();
    $table->timestamp('completed_at')->nullable();
    $table->timestamps();
});
```

### 3.3 Create withdrawal_requests table

**Migration:** `create_withdrawal_requests_table`
```php
Schema::create('withdrawal_requests', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->decimal('amount', 12, 2);
    $table->string('currency', 3)->default('RON');
    $table->string('status')->default('pending'); // pending, approved, rejected, completed
    $table->text('notes')->nullable();
    $table->text('admin_notes')->nullable();
    $table->timestamp('processed_at')->nullable();
    $table->timestamps();
});
```

---

## 4. Enums

### 4.1 DonationStatus
**File:** `app/Enums/DonationStatus.php`
- `Pending = 'pending'`
- `Completed = 'completed'`
- `Failed = 'failed'`
- `Refunded = 'refunded'`

### 4.2 WithdrawalStatus
**File:** `app/Enums/WithdrawalStatus.php`
- `Pending = 'pending'`
- `Approved = 'approved'`
- `Rejected = 'rejected'`
- `Completed = 'completed'`

---

## 5. Models

### 5.1 Donation Model
**File:** `app/Models/Donation.php`
- Relationships: `belongsTo(DonationBox)`
- Casts: `status` -> `DonationStatus`, `amount` -> `decimal:2`, `is_anonymous` -> `boolean`
- Scopes: `completed()`, `pending()`
- Accessor: `display_name` (returns "Anonymous" if is_anonymous)

### 5.2 WithdrawalRequest Model
**File:** `app/Models/WithdrawalRequest.php`
- Relationships: `belongsTo(User)`
- Casts: `status` -> `WithdrawalStatus`, `amount` -> `decimal:2`
- Scopes: `pending()`, `forUser(User $user)`

### 5.3 Update User Model
**File:** `app/Models/User.php`
- Add `withdrawable_balance` to `$fillable`
- Add cast: `'withdrawable_balance' => 'decimal:2'`
- Add relationships: `donations()`, `withdrawalRequests()`

### 5.4 Update DonationBox Model
**File:** `app/Models/DonationBox.php`
- Add relationship: `donations()`
- Add method: `isAcceptingDonations(): bool` (checks open + public/unlisted visibility)

---

## 6. Services

### 6.1 DonationService
**File:** `app/Services/DonationService.php`

Methods:
- `createPaymentIntent(DonationBox $box, array $data): array` - Creates Stripe PaymentIntent and Donation record
- `completePayment(string $paymentIntentId): void` - Called by webhook, updates status, increments amounts
- `failPayment(string $paymentIntentId): void` - Marks donation as failed
- `listForBox(DonationBox $box): Collection` - Get completed donations for display

### 6.2 WithdrawalService
**File:** `app/Services/WithdrawalService.php`

Methods:
- `createRequest(User $user, array $data): WithdrawalRequest` - Validates balance, creates request, deducts balance
- `cancel(WithdrawalRequest $request): void` - Returns funds to user balance
- `listForUser(User $user): Collection` - Get user's withdrawal requests

### 6.3 StripeWebhookService
**File:** `app/Services/StripeWebhookService.php`

Methods:
- `constructEvent(Request $request): Event` - Verifies signature, constructs Stripe Event
- `handleEvent(Event $event): void` - Routes to appropriate handler based on event type

---

## 7. Controllers & Routes

### 7.1 DonationController
**File:** `app/Http/Controllers/DonationController.php`
- `store(StoreDonationRequest, DonationBox)` - Creates payment intent, returns clientSecret
- `success(DonationBox)` - Shows success page

### 7.2 StripeWebhookController
**File:** `app/Http/Controllers/StripeWebhookController.php`
- `__invoke(Request)` - Handles Stripe webhook events

### 7.3 WithdrawalController
**File:** `app/Http/Controllers/WithdrawalController.php`
- `index(Request)` - Shows balance and withdrawal history
- `store(StoreWithdrawalRequest)` - Creates withdrawal request
- `destroy(WithdrawalRequest)` - Cancels pending request

### 7.4 Routes
**File:** `routes/web.php`

```php
// Public donation routes (guest accessible)
Route::post('donation-boxes/{donationBox}/donate', [DonationController::class, 'store'])
    ->name('donations.store');
Route::get('donation-boxes/{donationBox}/donate/success', [DonationController::class, 'success'])
    ->name('donations.success');

// Stripe webhook (excluded from CSRF)
Route::post('stripe/webhook', StripeWebhookController::class)
    ->name('stripe.webhook');

// Authenticated withdrawal routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('withdrawals', [WithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::post('withdrawals', [WithdrawalController::class, 'store'])->name('withdrawals.store');
    Route::delete('withdrawals/{withdrawalRequest}', [WithdrawalController::class, 'destroy'])->name('withdrawals.destroy');
});
```

### 7.5 Exclude Webhook from CSRF
**File:** `bootstrap/app.php`
```php
$middleware->validateCsrfTokens(except: ['stripe/webhook']);
```

---

## 8. Form Requests

### 8.1 StoreDonationRequest
**File:** `app/Http/Requests/StoreDonationRequest.php`
- `authorize()` -> `true` (guest accessible)
- Rules: donor_name, donor_email, amount (min:1, max:100000), message (optional), is_anonymous

### 8.2 StoreWithdrawalRequest
**File:** `app/Http/Requests/StoreWithdrawalRequest.php`
- Rules: amount (min:10, max:user's balance), notes (optional)

---

## 9. Frontend

### 9.1 TypeScript Types

**File:** `resources/js/types/donation.ts`
```typescript
export type DonationStatus = 'pending' | 'completed' | 'failed' | 'refunded';

export interface Donation {
  id: number;
  donation_box_id: number;
  donor_name: string;
  donor_email: string;
  message: string | null;
  is_anonymous: boolean;
  amount: number;
  currency: string;
  status: DonationStatus;
  completed_at: string | null;
  display_name: string; // "Anonymous" if is_anonymous
}
```

**File:** `resources/js/types/withdrawal.ts`
```typescript
export type WithdrawalStatus = 'pending' | 'approved' | 'rejected' | 'completed';

export interface WithdrawalRequest {
  id: number;
  amount: number;
  currency: string;
  status: WithdrawalStatus;
  notes: string | null;
  created_at: string;
}
```

### 9.2 Components & Pages

| File | Description |
|------|-------------|
| `resources/js/components/DonationForm.vue` | Stripe Elements form with card input |
| `resources/js/pages/donations/Success.vue` | Thank you page after donation |
| `resources/js/pages/withdrawals/Index.vue` | Balance display, withdrawal form, history |

### 9.3 Update Show.vue
**File:** `resources/js/pages/donation-boxes/Show.vue`
- Add `stripeKey` and `recentDonations` props
- Show DonationForm when box is open and user is not owner
- Display recent donations list (respecting anonymity)

---

## 10. Implementation Tasks (Ordered)

### Phase 1: Foundation
- [ ] 1. Install stripe/stripe-php and @stripe/stripe-js
- [ ] 2. Add environment variables to .env.example
- [ ] 3. Add Stripe config to config/services.php
- [ ] 4. Create DonationStatus enum
- [ ] 5. Create WithdrawalStatus enum

### Phase 2: Database
- [ ] 6. Create migration: add withdrawable_balance to users
- [ ] 7. Create migration: donations table
- [ ] 8. Create migration: withdrawal_requests table
- [ ] 9. Run migrations

### Phase 3: Models
- [ ] 10. Create Donation model with factory
- [ ] 11. Create WithdrawalRequest model with factory
- [ ] 12. Update User model (balance field, relationships)
- [ ] 13. Update DonationBox model (donations relationship, isAcceptingDonations)

### Phase 4: Services
- [ ] 14. Create DonationService
- [ ] 15. Create WithdrawalService
- [ ] 16. Create StripeWebhookService
- [ ] 17. Register services in AppServiceProvider

### Phase 5: Controllers & Routes
- [ ] 18. Create StoreDonationRequest
- [ ] 19. Create StoreWithdrawalRequest
- [ ] 20. Create DonationController
- [ ] 21. Create StripeWebhookController
- [ ] 22. Create WithdrawalController
- [ ] 23. Add routes to web.php
- [ ] 24. Exclude webhook from CSRF

### Phase 6: Frontend
- [ ] 25. Create TypeScript types (donation.ts, withdrawal.ts)
- [ ] 26. Create DonationForm.vue component
- [ ] 27. Update Show.vue with donation form
- [ ] 28. Create donations/Success.vue page
- [ ] 29. Create withdrawals/Index.vue page

### Phase 7: Testing
- [ ] 30. Create DonationServiceTest (unit)
- [ ] 31. Create WithdrawalServiceTest (unit)
- [ ] 32. Create DonationTest (feature)
- [ ] 33. Create WithdrawalTest (feature)
- [ ] 34. Create MocksStripe test trait

### Phase 8: Finalization
- [ ] 35. Run Pint for code formatting
- [ ] 36. Run full test suite
- [ ] 37. Manual testing with Stripe test mode

---

## 11. Critical Files Reference

| Purpose | File Path |
|---------|-----------|
| Donation model | `app/Models/Donation.php` |
| Payment logic | `app/Services/DonationService.php` |
| Webhook handling | `app/Http/Controllers/StripeWebhookController.php` |
| Stripe form | `resources/js/components/DonationForm.vue` |
| Donation box display | `resources/js/pages/donation-boxes/Show.vue` |
| Existing patterns | `app/Services/DonationBoxService.php` |

---

## 12. Verification

After implementation, verify:

1. **Install & Config**
   ```bash
   composer test:lint && composer test
   ```

2. **Stripe Test Mode**
   - Use test card: `4242 4242 4242 4242`
   - Verify payment intent creation
   - Test webhook with Stripe CLI: `stripe listen --forward-to localhost:8000/stripe/webhook`

3. **Full Flow Test**
   - Create donation box as authenticated user
   - Donate as guest (incognito)
   - Verify donation appears in box
   - Verify owner's withdrawable_balance increases
   - Request withdrawal as owner
   - Verify balance decreases

4. **Error Handling**
   - Test declined card: `4000 0000 0000 0002`
   - Verify user-friendly error messages
   - Verify donation status remains pending/failed