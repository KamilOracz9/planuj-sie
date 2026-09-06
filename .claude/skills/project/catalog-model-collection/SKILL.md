---
name: catalog-model-collection
description: Use whenever changing the Collection model, its migration, or CollectionController in this Laravel API — note this is App\Models\Collection, a catalog grouping, not a PHP/Eloquent Collection.
---

# Collection

**Naming note:** `App\Models\Collection` is a catalog grouping concept (a
curated set of Products), not `Illuminate\Support\Collection` or
`Illuminate\Database\Eloquent\Collection`. The model file's own header
comment warns that nothing in this namespace family may `use` those PHP
collection classes, to avoid a name clash.

## Relations

None declared on `Collection` itself — it has no `products()` inverse
relation. It's the *target* of `Product`'s `belongsToMany`, not a source of
relations (see [[catalog-model-product]] and [[catalog-hierarchy]]).

**Pivot:** `product_collection` (`product_id`, `collection_id`, both cascade
-delete, unique pair) — managed entirely from the `Product` side via
`App\Traits\HasCollections`, synced from the request's `collections` array
on `Product::saved()`. `Collection` has no code of its own that touches this
pivot.

## Traits & properties

Identical set to Brand/Series: `HasTranslations, HasCache, Sluggable,
HasFactory, HasAttributes, HasChannelVisibility, HasMediaCollections`.

- `$translatable = ['name', 'slug']`, `$sluggable = 'name'`,
  `#[Fillable(['id'])]`.
- No `ancestorGroupsForVisibility()` override → `[]` — a Collection's own
  visibility doesn't cascade from anything (it's Products that cascade
  *from* their Collections, via `Product::ancestorGroupsForVisibility()`,
  see [[catalog-channels]]).

## Table

`collections`: only `id`, timestamps. `collection_translations` holds
`name`/`slug`.

## Routes

- `PUT /collections/{id}`, `POST /collections`, `DELETE /collections/{id}`
- `GET /{locale}/collections` (index), `GET /{locale}/collections/select`,
  `GET /{locale}/collections/{id}` (show)

## Controller

`CollectionController` — thin, same boilerplate pattern as `BrandController`.

## Cross-cutting behavior

See [[catalog-channels]], [[catalog-attributes]], [[catalog-media]]. No
`HasPrices`.
