# Quick Reference Guide

## Slash Commands

| Command | Purpose | Usage |
|---------|---------|-------|
| `/prime` | Load codebase context | Before starting work |
| `/plan` | Create implementation plan | `plan "add feature X"` |
| `/implement` | Execute plan with validation | `implement .agents/plans/xxx.md` |
| `/validate` | Run all checks (Pint + Pest) | After making changes |
| `/commit` | Create atomic commit | After validation passes |
| `/create-pr` | Create pull request | When feature is ready |
| `/review-pr` | Comprehensive PR review | Before submitting PR |
| `/rca` | Root cause analysis | `rca "error message"` |
| `/check-ignores` | Audit suppressions | Check for @phpstan, eslint-disable |
| `/create-rules` | Generate CLAUDE.md | Analyze new codebases |
| `/prd` | Create product spec | `prd "feature description"` |

## Specialized Agents

| Agent | When | Command |
|-------|------|---------|
| test-writer | Need tests for code | Task with agent → test-writer |
| code-reviewer | Before committing | `/review-pr code` |
| pr-test-analyzer | Before PR | `/review-pr tests` |
| silent-failure-hunter | After error handling | `/review-pr errors` |
| comment-analyzer | Before PR | `/review-pr comments` |
| type-design-analyzer | New Services/types | `/review-pr types` |
| code-simplifier | Polish code | `/review-pr simplify` |

## Validation

```bash
# Run all checks
/validate

# Manual commands
composer test:lint      # Pint (PHP style)
composer test           # Pest (PHP tests)
npm run lint           # ESLint (Vue/JS)
```

## Development Cycle

```
1. /prime                          (Load context)
2. /plan "feature"                 (Create plan)
3. Write code                      (Follow CLAUDE.md)
4. /validate                       (Check quality)
5. Use test-writer agent           (Write tests)
6. /review-pr                      (Get feedback)
7. Fix issues                      (Apply suggestions)
8. /validate                       (Confirm fixed)
9. /commit                         (Create commit)
10. /create-pr                     (Open PR)
```

## Code Patterns Quick Lookup

### PHP Service (app/Services/)
```php
namespace App\Services;

class UserService {
    public function create(array $data): User {
        // Business logic here
        return User::create($data);
    }
}
```

### Controller (app/Http/Controllers/)
```php
class UserController extends Controller {
    public function __construct(
        private UserService $userService
    ) {}

    public function store(CreateUserRequest $request) {
        $user = $this->userService->create($request->validated());
        return redirect()->route('users.show', $user);
    }
}
```

### Vue Component (resources/js/components/)
```vue
<script setup lang="ts">
defineProps<{
  title: string
  count: number
}>()

const emit = defineEmits<{
  increment: []
}>()
</script>

<template>
  <div>{{ title }}: {{ count }}</div>
  <button @click="emit('increment')">+</button>
</template>
```

### Pest Test (tests/Unit/)
```php
describe('UserService', function () {
    it('creates user with valid data', function () {
        $user = app(UserService::class)->create([...]);
        expect($user->name)->toBe('John');
    });

    it('throws for duplicate email', function () {
        app(UserService::class)->create([...]);
    })->throws(Exception::class);
});
```

## File Locations

| Type | Location |
|------|----------|
| Services | `app/Services/` |
| Controllers | `app/Http/Controllers/` |
| Models | `app/Models/` |
| Requests | `app/Http/Requests/` |
| Unit Tests | `tests/Unit/` |
| Feature Tests | `tests/Feature/` |
| Vue Pages | `resources/js/pages/` |
| Vue Components | `resources/js/components/` |
| Migrations | `database/migrations/` |
| Seeders | `database/seeders/` |

## Common Issues

### Tests fail after changes
→ Run `/validate` to see errors
→ Use test-writer agent to generate missing tests

### Code review finds issues
→ Read the specific issue and CLAUDE.md
→ Fix the violation
→ Run `/validate` to confirm

### Unsure how to structure code
→ Read CLAUDE.md sections for patterns
→ Look at existing code in that directory
→ Use `/code-reviewer` for feedback

### Need to write tests
→ Use test-writer agent
→ Review existing tests in tests/ directory
→ Run `/validate` to confirm

## Configuration Files

| File | Purpose |
|------|---------|
| CLAUDE.md | Project conventions and patterns |
| composer.json | PHP dependencies and scripts |
| package.json | Node dependencies and scripts |
| phpunit.xml | Pest test configuration |
| pint.json | PHP code style config |
| .env | Environment variables |
| .gitignore | Files to ignore in git |

## Key Commands

```bash
# Development
composer dev          # Start dev server
npm run dev          # Frontend dev server
php artisan migrate  # Run migrations

# Testing
composer test        # Run all tests
composer test:lint   # Check PHP style
npm run lint        # Check Vue/JS style

# Database
php artisan tinker  # Interactive PHP shell
php artisan make:model ModelName  # New model
php artisan make:migration create_table  # Migration

# Utilities
php artisan route:list  # List all routes
php artisan cache:clear # Clear cache
```

## Type Hints (PHP)

```php
// Service method
public function create(array $data): User {
    return User::create($data);
}

// Controller action
public function show(User $user): Response {
    return response()->json($user);
}

// Form Request
public function rules(): array {
    return ['email' => ['required', 'email']];
}
```

## Vue Props (TypeScript)

```typescript
// Single type
defineProps<{
  title: string
}>()

// Optional prop
defineProps<{
  title?: string
}>()

// Default value
defineProps<{
  count: number
}>()

// With default
withDefaults(defineProps<{
  count?: number
}>(), { count: 0 })
```

## Assertions (Pest)

```php
// Equality
expect($user->name)->toBe('John')

// Truthiness
expect($user->active)->toBeTrue()
expect($user->active)->toBeFalse()

// Existence
expect($user->email)->not->toBeNull()
expect(User::find(1))->toBeNull()

// Collections
expect($users)->toHaveCount(3)
expect($data)->toHaveKey('id')

// Exceptions
expect(fn() => throw new Exception())
    ->toThrow(Exception::class)
```

## Remember

- ✅ Read CLAUDE.md first - it has all answers
- ✅ Run `/validate` frequently - catch issues early
- ✅ Use agents for feedback - they're very helpful
- ✅ Follow the patterns - consistency matters
- ✅ Test the critical paths - services and validation
- ✅ Keep controllers thin - business logic in services
- ✅ One-way data flow - Controller → Inertia → Component

**Status**: Ready to build! 🚀
