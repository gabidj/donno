---
name: handling-the-unhappy-path
title: Handling the Unhappy Path (Laravel)
source: "Laracon Online 2026 — Ryan Chandler"
version: 1.0
description: >
  Helps design Laravel apps that handle failures intentionally: sanitize and validate beyond basic rules,
  separate validation from domain/business logic, model domain guards as actions with meaningful exceptions
  (render/report/context), optionally return Result values for expected failures, and communicate errors
  to users with clear messages and appropriate UI treatments.
triggers:
  - "unhappy path / edge cases / error handling"
  - "Laravel FormRequest validation beyond basics"
  - "after() hook vs DataAwareRule decision"
  - "move controller checks into FormRequest"
  - "separate validation from business rules"
  - "design custom exceptions with render/report/context"
  - "errors as values / Result type"
  - "improve validation and flash messages"
inputs:
  - "User flows + what can go wrong"
  - "Existing FormRequests/controllers/actions (code snippets)"
  - "Domain rules (state transitions, constraints, time windows)"
  - "Target clients (web, API, Inertia) + desired HTTP statuses"
  - "Desired UX for validation vs system errors"
outputs:
  - "Where each check belongs (rules/prepareForValidation/after/DataAwareRule/authorize/action)"
  - "Refactor plan: controller → FormRequest → invokable validators → action"
  - "Custom exception design (message, context, render, report) + HTTP mapping"
  - "Optional Result type design + call-site handling pattern"
  - "User-facing copy improvements (messages()) + consolidation guidance"
  - "UI treatment recommendations (inline field errors vs toasts/callouts)"
principles:
  - "Validate shape; enforce domain in actions"
  - "Use prepareForValidation() to normalize inputs"
  - "Use after() for cross-field conflicts; DataAwareRule for single-field with context"
  - "Exceptions should carry meaning, context, and audience-aware rendering"
  - "Expected domain failures can be values (Result), not exceptions"
  - "Error messages should be actionable, specific, and placed appropriately"
---


Handling the unhappy path

---

Who am I?

Ryan Chandler

- Work: Senior Software Engineer, Laravel (Cloud)
- Location: Essex, United Kingdom
- Socials: @ryangjchandler (GitHub, X)

---

We spend most of our time on the happy path

```php
// Create an order
public function store(StoreOrderRequest $request)
{
    $order = Order::create($request->validated());

    return redirect()->route('orders.show', $order);
}

// Register a user
public function store(RegisterRequest $request)
{
    $user = User::create($request->validated());

    Auth::login($user);

    return redirect()->route('dashboard');
}

// Update profile settings
public function update(ProfileRequest $request)
{
    $request->user()->update($request->validated());

    return back()->with('status', 'Profile updated.');
}
```

ryangjchandler.co.uk

---

But users don’t stay on the happy path

```php
// Submitting an empty checkout form
$request->input('address'); // null
$request->input('payment_method'); // null

// Registering with a taken email
User::where('email', 'taken@example.com')
    ->exists(); // true

// Cancelling a shipped order
$order->status; // 'shipped'
$order->cancel(); // ... now what?

// Clicking "Place Order" twice
Order::where('user_id', $user_id)
    ->where('created_at', '>=', now()->subSecond())
    ->count(); // 2
```

---

The cost of getting it wrong

```php
// What the user sees:
abort(500);
// "500 | Server Error"
// No idea what they did wrong.
```

```php
// A silent failure:
$order->cancel();
// Order was already shipped...
// No exception, no flash message, just
// a redirect back with no feedback.
return back();
```

```php
// Or a generic catch-all:
try {
    $this->processOrder($order);
} catch (\Exception $e) {
    return back()->with(
        'error', 'Something went wrong.'
    );
}
```

---

What we’ll cover

1. Validation beyond the basics
2. Validation vs business logic
3. Designing exceptions intentionally
4. Errors as values
5. Communicating errors to users

---

Validation beyond the basics

---

The standard approach

```php
class StoreOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'product_id' => [
                'required', 'exists:products,id',
            ],
            'quantity' => [
                'required', 'integer', 'min:1',
            ],
            'shipping_address' => [
                'required', 'string', 'max:500',
            ],
        ];
    }
}
```

---

Sanitizing input with `prepareForValidation()`

```php
class StoreOrderRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $this->merge([
            'email' => Str::lower(
                $this->email
            ),
            'phone' => preg_replace(
                '/[^0-9+]/', '', $this->phone
            ),
        ]);
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required', 'email', 'unique:users',
            ],
            'phone' => [
                'required', 'string', 'min:10',
            ],
        ];
    }
}
```

---

Why bother sanitizing?

- `" Ryan@Example.COM "` → `"ryan@example.com"`
    - Consistent casing so unique checks don’t miss duplicates
- `"+44 (0) 7911 123456"` → `"+4407911123456"`
    - Strip formatting so length rules actually mean something
- `"My Blog Post!"` → `"my-blog-post"`
    - Derive fields like slugs so the user doesn’t have to think about it

---

So far we’ve got…

- **Validation rules** for checking individual fields
- **`prepareForValidation()`** for sanitizing input before rules run

But what about checks that depend on **multiple fields together**?

---

This often ends up in the controller

```php
public function store(StoreBookingRequest $request)
{
    // Validation passed, but...
    $overlap = Booking::where('room_id', $request->room_id)
        ->whereBetween('date', [
            $request->start_date,
            $request->end_date,
        ])
        ->exists();

    if ($overlap) {
        return back()->withErrors([
            'room_id' => 'This room is already booked.',
        ]);
    }

    $sum = $request->collect('allocations.*.percentage')
        ->sum();

    if ($sum !== 100) {
        return back()->withErrors([
            'allocations' => 'Allocations must total 100%.',
        ]);
    }

    Booking::create($request->validated());
}
```

ryangjchandler.co.uk

---

The `after()` hook

Runs **after** individual field rules have passed, so you know the data is well-formed.

- Database lookups with multiple fields: *“this room isn’t already booked for these dates”*
- Array relationships: *“allocations must total 100%”, “no duplicate product IDs”*
- Conditional logic: *“if payment is ‘card’, the card must not be expired”*
- Anything that needs all the **validated** data before it can run

---

Moving it to the form request

```php
class StoreBookingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'room_id' => ['required', 'exists:rooms,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'allocations.*.percentage' => ['required', /* ... */],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $overlap = Booking::where('room_id', $this->room_id)
                    ->whereBetween('date', [
                        $this->start_date,
                        $this->end_date,
                    ])
                    ->exists();

                if ($overlap) {
                    $validator->errors()->add(
                        'room_id', 'This room is already booked.'
                    );
                }
            },
        ];
    }
}
```

---

```php
public function after(): array
{
    return [
        function (Validator $validator) {
            if ($validator->errors()->any()) {
                return;
            }

            $overlap = Booking::where('room_id', $this->room_id)
                ->whereBetween('date', [
                    $this->start_date,
                    $this->end_date,
                ])
                ->exists();

            if ($overlap) {
                $validator->errors()->add(
                    'room_id', 'This room is already booked.'
                );
            }
        },
    ];
}
```

---

Extracting to invokable classes

```php
class StoreBookingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'room_id' => ['required', 'exists:rooms,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'allocations.*.percentage' => ['required', /* ... */],
        ];
    }

    public function after(): array
    {
        return [
            new NoOverlappingBooking,
            new AllocationsMustTotal(100),
        ];
    }
}
```

---

What the invokable class looks like

```php
class NoOverlappingBooking
{
    public function __invoke(
        Validator $validator,
    ): void {
        $data = $validator->validated();

        $overlap = Booking::query()
            ->where('room_id', $data['room_id'])
            ->whereBetween('date', [
                $data['start_date'],
                $data['end_date'],
            ])
            ->exists();

        if ($overlap) {
            $validator->errors()->add(
                'room_id',
                'This room is already booked'
                . ' for those dates.',
            );
        }
    }
}
```

---

Another use case: conditional checks

```php
class CardMustNotBeExpired
{
    public function __invoke(
        Validator $validator,
    ): void {
        $data = $validator->validated();

        if ($data['payment_method'] !== 'card') {
            return;
        }

        $expiry = Carbon::createFromFormat(
            'm/y', $data['card_expiry'],
        );

        if ($expiry->isPast()) {
            $validator->errors()->add(
                'card_expiry',
                'This card has expired.',
            );
        }
    }
}
```

---

Custom rules with `DataAwareRule`

```php
class MaxQuantityForProduct implements
    ValidationRule,
    DataAwareRule
{
    protected array $data = [];

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(
        string $attribute,
        mixed $value,
        Closure $fail,
    ): void {
        $product = Product::find(
            $this->data['product_id']
        );

        if ($value > $product->max_quantity) {
            $fail(
                'You can only order up to '
                . $product->max_quantity
                . '.'
            );
        }
    }
}
```

---

Using it in a form request

```php
class StoreOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'product_id' => [
                'required', 'exists:products,id',
            ],
            'quantity' => [
                'required',
                'integer',
                'min:1',
                new MaxQuantityForProduct,
            ],
            'shipping_address' => [
                'required', 'string', 'max:500',
            ],
        ];
    }
}
```

---

`after()` hook vs `DataAwareRule`

- `after()` hook — runs after all rules pass  
  Best for checks that need the full validated dataset, or that span multiple fields
- `DataAwareRule` — runs as part of a single field’s rules  
  Best for reusable rules that need context from other fields, but validate one field

- Use `after()` when: “Do these fields conflict with each other?”
- Use `DataAwareRule` when: “Is this field valid given the other fields?”

---

Where does this check belong?

- Is the data dirty? → `prepareForValidation()` — clean it before checking
- Is a single field invalid? → `rules()` — standard validation rules
- Is a single field invalid *given other fields*? → `DataAwareRule` — reusable, context-aware
- Do multiple fields conflict? → `after()` hook — runs when all fields are valid
- Is the user allowed? → `authorize()` — built into form requests
- Is the action allowed *right now*? → Action class — domain logic, custom exceptions

---

Validation vs business logic

---

Two different questions

- **Validation:** “Is this data well-formed?”  
  Required fields, correct types, valid formats, uniqueness
- **Business logic:** “Is this action allowed right now?”  
  State checks, authorization, plan limits, domain rules

---

When they get mixed together

- Validation errors appear for business rule violations
- Business logic gets buried in form requests
- Hard to test either in isolation
- Error messages don’t make sense to the user

---

What about `authorize()`?

```php
class CancelOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can(
            'cancel', $this->route('order'),
        );
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string'],
        ];
    }
}
```

---

Business logic in the wrong place

```php
public function after(): array
{
    return [
        function (Validator $validator) {
            $order = Order::find(
                $this->route('order')
            );

            if ($order->status === 'shipped') {
                $validator->errors()->add(
                    'order',
                    'Cannot cancel a shipped order.'
                );
            }
        },
    ];
}
```

---

Pull it into an action

```php
class CancelOrder
{
    public function handle(
        Order $order,
        string $reason,
    ): void {
        if ($order->status === 'shipped') {
            throw new OrderAlreadyShippedException(
                $order,
            );
        }

        $order->update([
            'status' => 'cancelled',
            'cancelled_reason' => $reason,
        ]);
    }
}
```

---

Actions with multiple guards

```php
class RefundOrder
{
    public function handle(
        Order $order,
    ): void {
        if ($order->status === 'refunded') {
            throw new OrderAlreadyRefundedException(
                $order,
            );
        }

        if ($order->status !== 'completed') {
            throw new OrderNotCompletedException(
                $order,
            );
        }

        if ($order->completed_at < now()->subDays(30)) {
            throw new RefundWindowExpiredException(
                $order,
                days: 30,
            );
        }

        $order->update(['status' => 'refunded']);

        RefundPayment::dispatch($order);
    }
}
```

---

Designing exceptions intentionally

---

Not just “something went wrong”

- Generic exceptions tell the user nothing
- They tell the developer nothing in logs either
- Custom exceptions carry **meaning and context**
- They can control how they’re rendered and reported

---

Anatomy of a custom exception

```php
class OrderAlreadyShippedException extends Exception
{
    public function __construct(
        protected Order $order,
    ) {
        parent::__construct(
            "Order #{$order->id} has already been shipped."
        );
    }

    public function context(): array
    {
        return [
            'order_id' => $this->order->id,
            'status' => $this->order->status,
            'shipped_at' => $this->order->shipped_at,
        ];
    }
}
```

---

Controlling the response with `render()`

```php
public function render(Request $request)
{
    if ($request->expectsJson()) {
        return response()->json([
            'message' => $this->getMessage(),
            'order_id' => $this->order->id,
        ], 409);
    }

    return back()->with(
        'error',
        'This order has already been shipped'
        . ' and cannot be cancelled.'
    );
}
```

---

Why `render()` matters

- The exception **knows its own audience** — no one else needs to
- Controllers stay clean — no `try / catch`, no response logic for errors
- You choose the **right HTTP status** — 409 Conflict, 422, 403
- The same exception works for web, API, and Inertia — one class, multiple formats
- Without `render()`, every caller has to know how to present the error

---

Controlling logging with `report()`

```php
public function report(): bool
{
    Log::info(
        'Cancellation attempted on shipped order',
        $this->context(),
    );

    return false;
}
```

---

Why `report()` matters

- Not every exception is an **error** — some are expected behaviour
- Without `report()`, every exception hits your error tracker the same way
- Expected exceptions **drown out** real bugs in Nightwatch
- `return false` = “I’ve handled this, stop here”
- `return true` (or omit) = “Log this normally, I just wanted to add context”

---

The complete exception

```php
class OrderAlreadyShippedException extends Exception
{
    public function __construct(
        protected Order $order,
    ) {
        parent::__construct(
            "Order #{$order->id} has already been shipped."
        );
    }

    public function context(): array
    {
        return [
            'order_id' => $this->order->id,
            'status' => $this->order->status,
            'shipped_at' => $this->order->shipped_at,
        ];
    }

    public function render(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->getMessage(),
                'order_id' => $this->order->id,
            ], 409);
        }

        return back()->with(
            'error',
            'This order has already been shipped'
            . ' and cannot be cancelled.'
        );
    }

    public function report(): bool
    {
        Log::info(
            'Cancellation attempted on shipped order',
            $this->context(),
        );

        return false;
    }
}
```

---

User-friendly vs developer-friendly

```json
// What the user sees (render):
{
  "message": "This order has already been shipped and cannot be cancelled.",
  "order_id": 472
}
```

```text
// What the developer sees (Nightwatch):
[2026-02-12 14:23:01] local.INFO:
    Cancellation attempted on shipped order.
{
  "order_id": 472,
  "status": "shipped",
  "shipped_at": "2026-02-10 09:15:00",
  "user_id": 1234,
  "ip": "192.168.1.1"
}
```

---

Errors as values

---

Exceptions are invisible to the caller

```php
class CancelOrder
{
    public function handle(
        Order $order,
        string $reason,
    ): void {
        if ($order->status === 'shipped') {
            throw new OrderAlreadyShippedException(
                $order,
            );
        }

        $order->update([
            'status' => 'cancelled',
            'cancelled_reason' => $reason,
        ]);
    }
}
```

---

A Result carries success or failure

```php
class Result
{
    private function __construct(
        private readonly bool $ok,
        private readonly mixed $val = null,
        private readonly mixed $err = null,
    ) {}

    public static function ok(mixed $value = null): static
    {
        return new static(true, val: $value);
    }

    public static function err(mixed $error): static
    {
        return new static(false, err: $error);
    }

    public function isOk(): bool
    {
        return $this->ok;
    }

    public function isErr(): bool
    {
        return ! $this->ok;
    }
}
```

---

Return the result, don’t throw

```php
class CancelOrder
{
    public function handle(
        Order $order,
        string $reason,
    ): Result {
        if ($order->status === 'shipped') {
            return Result::err(
                'This order has already been shipped and cannot be cancelled.'
            );
        }

        $order->update([
            'status' => 'cancelled',
            'cancelled_reason' => $reason,
        ]);

        return Result::ok($order);
    }
}
```

---

(Controller snippet in your screenshot is too washed out to transcribe reliably—send a sharper crop of the code area and I’ll extract it exactly.)

---

Why errors as values?

- **No try/catch** — errors flow through the return value, not control flow
- **Forced handling** — the return type announces that failure is possible; ignoring the Result means ignoring a value
- **Locality** — success and failure are documented at the call site, not in a distant exception class
- **Testability** — `assertEquals(Result::err('...'), $result)` is simpler than asserting thrown exceptions

---

Exceptions vs Results

- **Exceptions** for truly exceptional situations — third-party failures, infrastructure down, unrecoverable errors
- **Results** for expected domain failures — business rule violations, invalid state transitions
- Both can coexist — your action returns a Result, your HTTP client still throws

---

Communicating errors to users

---

Default messages aren’t always enough

- “The email field is required.” — fine, clear enough
- “The email has already been taken.” — taken by who? Can I log in instead?
- “The value must be at least 8.” — 8 what? Characters? Digits?
- “The field is invalid.” — thanks for nothing

---

Crafting messages with `messages()`

```php
class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => [
                'required', 'email', 'unique:users',
            ],
            'password' => [
                'required', 'string', 'min:8',
            ],
            'name' => [
                'required', 'string', 'min:1',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' =>
                'That email is already registered.'
                . ' Did you mean to log in?',
            'password.min' =>
                'Your password must be at least '
                . ' 8 characters long.',
        ];
    }
}
```

---

When to be explicit, when to consolidate

Be explicit when the fix isn’t obvious:

- `email.unique` — “That email is already registered. Did you mean to log in?”
- `coupon.exists` — “We couldn’t find that coupon code. Double-check for typos.”
- `file.max` — “The file is too large. Maximum size is 10MB.”

Consolidate when multiple rules say the same thing:

- `name.required`, `name.string`, `name.min:1` all just mean “Please enter your name.”

---

Two categories of errors

- “You did something wrong”  
  Validation errors — the user can fix this by changing their input
- “Something went wrong”  
  System errors, business logic failures — the user might not be able to fix this

These need **different UI treatments**.

---

Validation errors: inline, next to the field

- Show the error **right next to the field** that caused it
- Highlight the field itself (red border, icon)
- Don’t clear the user’s input — let them fix it
- Scroll to the first error if the form is long

---

Passing validation errors in Laravel

```php
// Laravel handles this automatically.
// After a failed form request validation,
// the user is redirected back with:

$errors->get('email');

// => ["That email is already registered.
//     Did you mean to log in?"]
```

```blade
{{-- In your Blade template --}}
<input name="email" type="email"
    @class([
        'border-red-500' => $errors->has('email'),
    ])
>

@error('email')
    <p class="text-red-500 text-sm mt-1">
        {{ $message }}
    </p>
@enderror
```

---

System errors: toasts and callouts

- Business logic failures: **flash messages / toasts**  
  “This order has already been shipped and cannot be cancelled.”
- Server errors: **a clear error state**, not a stack trace  
  “Something went wrong. Please try again or contact support.”
- Never show raw exception messages to users
- Always give the user a **next step**
- In your layout, one `@if (session('error'))` block covers **all** business logic errors

---

Wrap-up

---

What we covered

1. **Validation beyond the basics** — `prepareForValidation()`, `after()`, `DataAwareRule`
2. **Validation vs business logic** — keep them separate, let your domain guard its own state
3. **Designing exceptions** — `render()`, `report()`, `context()` for control and clarity
4. **Errors as values** — Result types as an alternative to exceptions for domain failures
5. **Communicating errors** — craft messages that help, show them in the right place

---

Errors are a feature, not an afterthought.
