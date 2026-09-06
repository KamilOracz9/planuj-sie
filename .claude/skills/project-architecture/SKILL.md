---
name: project-architecture
description: Use whenever adding or changing backend data-flow code in this Laravel API (a new model, endpoint, query, or piece of business logic) — dictates the layering between Models, QueryBuilders, Repositories, Collections, Services and Requests, and where each kind of logic must live.
---

# Project architecture (api/)

This API follows a strict layering for how data moves from the database to
the HTTP response. Each kind of logic has exactly one place it's allowed to
live. When adding or changing backend code, put new logic in the layer it
belongs to instead of inlining it in a controller or wherever is convenient.

```
HTTP response ── cached whole, globally, by spatie/laravel-responsecache
   │
Request (validation)
   │
Controller (thin: validate → call Service/Repository → return Resource)
   │
Service (business logic, orchestration, side effects)
   │
Repository (the ONLY place that executes queries; wraps results in swappable-cache)
   │
Collection (post-fetch operations on a Collection of models)
   │
QueryBuilder (composable, non-terminal query building, per model)
   │
Model (Eloquent) — the only way this app talks to the database
```

Response caching sits above all of this and is orthogonal to it: every
response this API returns is cached via `spatie/laravel-responsecache`,
applied globally (not opted into per controller) — see [[caching-strategy]]
for how it interacts with `swappable-cache` at the repository layer below
it.

## 1. Models — `App\Models` — all DB access is through Eloquent

All communication with the database goes through Eloquent models. Don't
drop into raw `DB::table()`/raw SQL, and don't hand-write PDO. If a query
needs a join or a table that isn't a straightforward relation, express it on
the model's dedicated QueryBuilder (below) rather than reaching for `DB::`
directly. (`BaseController::show()`'s `DB::table($modelTranslation::tableName())...`
call is legacy — don't copy that pattern into new code; resolve translations
through the model/query builder instead.)

## 2. QueryBuilders — `App\QueryBuilders\{Model}QueryBuilder`

Any query logic that does **not** end in a terminal call (`get()`, `first()`,
`count()`, `exists()`, ...) — joins, `select()`s, `where()` scopes, ordering,
translation joins via [[translation-strategy]]'s `withTranslation()`/
`withTranslations()` — belongs in that model's query builder, extending
`App\QueryBuilders\BaseQueryBuilder` (see the existing `CollectionQueryBuilder`,
`ProductQueryBuilder`, etc. for the pattern: `protected string $modelClass`
+ a `listSelect()` method). A query builder method must always return a
builder (`$this`/chainable) — it never calls `->get()` or otherwise executes.

## 3. Repositories — `App\Http\Repositories\{Model}Repository` — the only place that fetches data

**Data fetching happens only in repositories.** No other layer (controller,
service, collection) is allowed to call a terminal query method
(`->get()`, `->first()`, `->paginate()`, ...) directly — it goes through a
repository method instead. This is the current codebase's biggest deviation
from the target architecture: `BaseController::index()/select()/show()`
build and execute queries directly, and `AttributeRepository` caches with
the raw `Cache` facade — both are legacy patterns; **new code must not
replicate either.**

**Every repository method's return value must be cached with
`kamiloracz9/swappable-cache`** (see [[caching-strategy]]), not
`Cache::remember()`/`cache()->remember()`:

```php
namespace App\Http\Repositories;

use App\Models\Product;
use Kamiloracz9\SwappableCache\Facades\SwappableCache;

class ProductRepository
{
    public static function activeProducts(): \App\Collections\ProductCollection
    {
        return SwappableCache::remember(
            key: 'active-products',
            resolve: fn () => Product::queryBuilder()->listSelect()->where('active', true)->get(),
            fingerprint: fn () => Product::where('active', true)->max('updated_at'),
            checkInterval: 60,
            modelClass: Product::class,
        );
    }
}
```

A repository method's job is: call the model's query builder, execute it,
return the (cached) result — nothing else. Business rules, transformations,
or side effects don't belong here (see Services).

## 4. Collections — `App\Collections\{Model}Collection`

Operations on an already-fetched collection of a given model (grouping,
filtering in memory, computing derived aggregates across the set, mapping to
a different shape) belong in that model's own collection class, not inlined
where the collection happens to be used:

```php
namespace App\Collections;

use Illuminate\Database\Eloquent\Collection;

class ProductCollection extends Collection
{
    public function activeOnly(): static
    {
        return $this->filter(fn ($product) => $product->active);
    }
}
```

Wire it up on the model:

```php
public function newCollection(array $models = []): ProductCollection
{
    return new ProductCollection($models);
}
```

## 5. Services — `App\Services\{Name}Service`

Everything that isn't query-building, data-fetching, or collection
transformation — business rules, orchestrating multiple repositories,
side effects (dispatching jobs, sending notifications, writing files),
multi-step operations — lives in a service. Controllers call services (or,
for a plain read, a repository directly); services call repositories, never
the other way around.

## 6. Requests — `App\Http\Requests\{Model}Request`

All incoming request data is validated, and that validation lives in its
own `FormRequest` class (`extends BaseRequest`, per the existing
`ProductRequest`/`LocaleRequest`/... pattern) — never inline `$request->validate([...])`
calls in a controller, and never hand-rolled manual checks.

## Decision rule when adding new code

- Building a query but not running it yet → QueryBuilder.
- Actually running a query and returning data → Repository (cached via
  swappable-cache).
- Reshaping/filtering a Collection you already have → that model's
  Collection class.
- Anything else (business logic, orchestration, side effects) → Service.
- Anything arriving from an HTTP request → validate it in a Request class
  first.
