---
name: write-better-abstractions
title: Write Better Abstractions (Description Over Instructions)
source: "Laracon Online 2026 — Dan Harrin (Filament)"
version: 1.0
description: >
  Helps design APIs/DSLs that express intent (descriptions) while hiding mechanical steps,
  with layered configuration, clean escape hatches (methods/closures), deferred evaluation,
  and errors translated from internals into user-facing abstraction language.
triggers:
  - "design an API/abstraction/DSL"
  - "make configuration flexible without a bad DSL"
  - "add escape hatches (closures/methods)"
  - "improve error messages for an abstraction"
  - "review whether config vs code is appropriate"
inputs:
  - "Goal / user intent"
  - "Current code or proposed API surface"
  - "Known edge cases & failure modes"
  - "Constraints (perf, DX, backwards compatibility)"
outputs:
  - "Recommended abstraction surface (description-first)"
  - "Layered progression (simple → advanced → escape hatch)"
  - "Closure/method escape hatch signature (inject only what’s needed)"
  - "Deferred description points (evaluate at runtime)"
  - "Translated failure messages (internals → abstraction terms)"
  - "Config-vs-code decision using the Enumeration Test"
principles:
  - "Hide the mechanical; expose the meaningful"
  - "Configuration needs escape velocity (never hit a wall)"
  - "If options are infinite, use methods/closures"
  - "Every abstraction is a bet—make it survive being wrong"
---

# Write Better Abstractions

Presented by Laravel Filament


### Description Over Instructions
They declare intent. You handle machinery.


**Instructions (step by step)**

```php
class ProductImporter extends Importer
{
    public function processRow(array $row): void
    {
        Product::create([
            'name' => $row['name'],

            'price' => (float) str_replace(
                ['$', ','],
                '',
                $row['price'],
            ),

            'active' => in_array(
                strtolower($row['active']),
                ['yes', 'true', '1'],
            ),
        ]);
    }
}
```

**Description (what you want)**

```php
class ProductImporter extends Importer
{
    protected string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name'),

            ImportColumn::make('price')
                ->numeric(),

            ImportColumn::make('active')
                ->boolean(),
        ];
    }
}
```

### Layers of Description

```php
// Layer 3: Full escape hatch
class ImportColumn
{
    protected bool $isNumeric = false;

    protected ?int $decimalPlaces = null;

    protected ?Closure $castStateUsing = null;

    public function numeric(?int $decimalPlaces = null): static { /* ... */ }

    public function castStateUsing(?Closure $callback): static
    {
        $this->castStateUsing = $callback;

        return $this;
    }
}
```


Configuration needs escape velocity
Start simple, progressively gain control, never hit a wall.
Without escape hatches, you bet your description covers everything.


### Deferred Description

```php
// Store the closure, defer evaluation to a getter
class ImportAction
{
    protected int | Closure $maxRows = 100000;

    public function maxRows(int | Closure $rows): static
    {
        $this->maxRows = $rows;

        return $this;
    }

    public function getMaxRows(): int
    {
        return value($this->maxRows);
    }
}
```



### Clean Closures

```php
// Forcing all parameters on every closure
->castStateUsing(function (mixed $state, array $data, Model $record, array $options) {
    // Most closures only need 1-2 of these...
})
```

```php
// Each closure declares only what it needs
->castStateUsing(function (array $data): int {
    return match ($data['currency']) {
        'JPY' => (int) $data['price'],
        default => (int) ($data['price'] * 100)
    };
})

->castStateUsing(function (Model $record): ?int {
    return $record->organization_id;
})
```

```php
// Reflect on the closure, inject by parameter name and type
(new ReflectionFunction($closure))->getParameters();
```

Tip:
Closures should feel effortless
Escape hatches must feel as clean as the description itself.
Defer evaluation. Inject only what each closure requests.

Tip:
The Abstraction Paradox
Abstractions hide complexity. That’s the point.
When things fail, users can’t debug hidden internals.
Failures must be translated from implementation to abstraction.

### Implementation vs Abstraction Failures

Implementation
A typo, but the error points at framework internals.

BadMethodCallException:
Call to undefined method
Illuminate\Database\Eloquent\Builder::category()
(ForwardCalls.php:71)
Abstraction
The developer sees the problem immediately.

ImportColumn [category]:
Relationship "category" is not defined
on App\Models\Product.

User attachment

Tip:
Hide the how, translate the why

"BadMethodCallException in ForwardsCalls.php" → "Relationship 'category' is not defined on Product"
In: translate config to internals. Out: translate errors to config.

### The Required Method

```php
class ProductImporter extends Importer
{
    public function resolveRecord(): ?Product
    {
        return Product::firstOrNew([
            'sku' => $this->data['sku'],
        ]);
    }
}
```

Upsert logic: new record, or update to existing?

The framework could guess. It doesn't.

Record uniqueness is domain logic: different for each use case.

Example: The Wrong Abstraction

```php
// Seems simple...
class ProductImporter extends Importer
{
    protected static ?string $upsertKey = 'sku';
}
```

```php
// Then someone needs two columns...
class ProductImporter extends Importer
{
    protected static array $upsertKeys = ['sku', 'warehouse_id'];
}
```

```php
// Then transformed values... You're building a bad DSL.
class ProductImporter extends Importer
{
    protected static array $upsertKeys = ['sku', 'warehouse_id'];

    protected static array $upsertTransforms = ['sku' => 'strtolower'];
}
```

```php
// Just use PHP.
public function resolveRecord(): ?Product
{
    return Product::query()
        ->whereRaw('LOWER(sku) = ?', [strtolower($this->data['sku'])])
        ->where('warehouse_id', $this->data['warehouse_id'])
        ->first() ?? new Product();
}
```

### The Enumeration Test

Can you enumerate the valid options?

Yes  
Finite choices  
→ Configuration

No  
Infinite choices  
→ Method / Closure


Tip:
Hide the mechanical, expose the meaningful
Mechanical work: same every time, no judgment. Abstract it.
Meaningful work: varies by domain, requires judgment. Expose it.

### Description Over Instructions

1. Layers: so users choose their depth
2. Closures: so they adapt to runtime
3. Translated failures: so errors make sense
4. Limits: so some things stay as code

In many places where code depends on other code.

Tip:
Every abstraction is a bet

Make it a bet that survives being wrong.




This talk was recorded at Laracon Online 2026
Author: Dan Harrin
Co-creator of Filament
