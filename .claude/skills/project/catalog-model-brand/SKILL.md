---
name: catalog-model-brand
description: Use whenever changing the Brand model, its migration, or BrandController in this Laravel API.
---

# Brand

## Relations

None. Brand has no parent, no child, and no reverse relation to `Product` is
declared on `Brand` itself — `Product::brand()` holds the (optional) inverse
`belongsTo`. See [[catalog-hierarchy]] — Brand is top-level, not nested
under anything.

## Traits & properties

`HasTranslations, HasCache, Sluggable, HasFactory, HasAttributes,
HasChannelVisibility, HasMediaCollections` (implements `HasMedia`).

- `public array $translatable = ['name', 'slug'];`
- `public string $sluggable = 'name';`
- `#[Fillable(['id'])]`
- No `ancestorGroupsForVisibility()` override → `[]` (no cascade; see
  [[catalog-channels]]).

## Table

`brands`: only `id`, timestamps. Zero scalar columns of its own — `name`/
`slug` live entirely in `brand_translations` (unique `(brand_id, locale)`),
per [[translation-strategy]].

## Routes

- `PUT /brands/{id}`, `POST /brands`, `DELETE /brands/{id}`
- `GET /{locale}/brands` (index), `GET /{locale}/brands/select`,
  `GET /{locale}/brands/{id}` (show)

## Controller

`BrandController` is thin: only overrides `$listCacheKey`/
`$selectCacheKey`/`$resourceClass`, constructs `Brand`/`BrandTranslation`,
and implements `update`/`create` with the same boilerplate shared by every
catalog controller (`findOrFail` → `update($request->validated())` /
`new Model($request->validated())->save()`). No custom logic.

## Cross-cutting behavior that applies to Brand

See [[catalog-channels]] (visibility), [[catalog-attributes]] (custom
fields), [[catalog-media]] (images/documents). Brand does **not** use
`HasPrices` — it isn't priced directly.
