---
name: catalog-model-variant
description: Use whenever changing the Variant model, its migration, or VariantController in this Laravel API — a Variant belongs to exactly one Product and has its own independent translations/prices/attributes/media, not inherited copies.
---

# Variant

## Relations

```php
public function product(): BelongsTo { return $this->belongsTo(Product::class); }
```

That's the only relation. `product_id` is **NOT NULL**
(`foreignIdFor(Product::class)->cascadeOnDelete()`) — deleting a Product
cascades and deletes its Variants at the DB level. This is the **only**
strict, mandatory parent/child relation anywhere in the catalog hierarchy
(see [[catalog-hierarchy]]). No relation to Brand/Series/Collection/Category
directly — reach those only via `variant->product->brand` etc.

```php
public function ancestorGroupsForVisibility(): array { return [[$this->product]]; }
```

A Variant's channel visibility depends only on its parent Product's
visibility (which in turn cascades through Product's own brand/series/
collections) — see [[catalog-channels]].

## Traits & properties

`HasTranslations, HasCache, Sluggable, HasAttributes, HasChannelVisibility,
HasPrices, HasMediaCollections` — **no `HasCollections`** (a Variant is
never itself a member of a Collection — only Products can be), **no
`HasFactory`**.

- `$translatable = ['name', 'slug', 'description', 'short_description']` —
  same shape as Product.
- `$sluggable = 'name'`.
- `#[Fillable(['id', 'product_id'])]`.

## A Variant does NOT inherit its parent Product's data — it has its own rows

This is the most important thing to know about Variant. Nothing here is
copied from the Product via a DB relation:

- **Translations**: its own `variant_translations` rows — name/slug/
  description/short_description can differ entirely from the parent
  Product's.
- **Prices**: its own polymorphic `Price` rows (`model_type =
  Variant::class`), separate from the Product's. They're **additive**, not
  a replacement — `ProductController::priceBreakdown()` computes
  `final_price = product_price + variant_price + attributes_price`. See
  [[catalog-pricing]].
- **Attributes**: its own polymorphic `AttributeValue` rows. A Variant's own
  value for a given `attribute_id` **overrides** the Product's value for
  that same attribute at read time (in `priceBreakdown()`); attributes the
  Variant doesn't override are inherited from the Product **at the
  application layer**, not via any DB relation. See [[catalog-attributes]].
- **Media**: independent, via its own `HasMediaCollections` rows. See
  [[catalog-media]].

## Table

`variants`: `id`, `product_id` (FK, cascade-delete, NOT NULL), timestamps.
`variant_translations`: `name`, `slug` (unique), `short_description`,
`description` — identical shape to `product_translations`.

## Routes

- `PUT /variants/{id}`, `POST /variants`, `DELETE /variants/{id}` — **no
  `select` endpoint**, unlike the other five catalog models (no
  `$selectCacheKey` on `VariantController`).
- `GET /{locale}/variants` (index), `GET /{locale}/variants/by-product/{productId}`
  (dedicated, **uncached** per-product listing), `GET /{locale}/variants/{id}`
  (show).

Variant **does** have its own top-level CRUD routes (`/variants`,
`/{locale}/variants/{id}`) — it is independently addressable, not
exclusively nested under `/products/{id}/variants`. The `by-product`
endpoint is just a filtered list, not a different resource shape.

## Controller

`VariantController` — mostly thin (standard `update`/`create` boilerplate)
plus one custom action, `byProduct()`, which uses
`VariantQueryBuilder::filterByProduct()` + `filterByChannel()` for an
uncached, single-product admin lookup.
