---
name: catalog-model-product
description: Use whenever changing the Product model, its migration, ProductController, or price-breakdown/collection logic in this Laravel API.
---

# Product

## Relations

```php
public function brand(): BelongsTo   { return $this->belongsTo(Brand::class); }   // brand_id, nullable
public function series(): BelongsTo  { return $this->belongsTo(Series::class); }  // series_id, nullable
public function variants(): HasMany  { return $this->hasMany(Variant::class); }
// via HasCollections:
public function collections()        { return $this->belongsToMany(Collection::class, 'product_collection', 'product_id', 'collection_id'); }
```

**No relation to `Category` at all.** Product's real ancestry is three
independent, optional, parallel groupings — Brand (singular), Series
(singular), Collection (many-to-many) — not a chain through all of them. See
[[catalog-hierarchy]] for the full picture.

## Traits & properties

`HasTranslations, HasCache, Sluggable, HasAttributes, HasCollections,
HasChannelVisibility, HasPrices, HasMediaCollections` — **no `HasFactory`**.

- `$translatable = ['name', 'slug', 'description', 'short_description']`.
- `$sluggable = 'name'`.
- `#[Fillable(['id', 'brand_id', 'series_id'])]`.
- `ancestorGroupsForVisibility()`:
  ```php
  public function ancestorGroupsForVisibility(): array
  {
      $groups = [];
      if ($this->brand_id) { $groups[] = [$this->brand]; }
      if ($this->series_id) { $groups[] = [$this->series]; }
      if ($this->collections->isNotEmpty()) { $groups[] = $this->collections->all(); }
      return $groups;
  }
  ```
  Each present dimension is ANDed with the others; within the collections
  group, membership in *any one* visible collection suffices (OR). Full
  detail: [[catalog-channels]].

## Table

Base migration creates only `id`, timestamps; a later migration adds
`brand_id` (nullable, FK→brands, `nullOnDelete`) and `series_id` (nullable,
FK→series, `nullOnDelete`). Final columns: `id, brand_id, series_id,
created_at, updated_at`. `product_translations` adds `name`, `slug`
(unique), `short_description` (255), `description` (500).

## Routes

- `PUT /products/{id}`, `POST /products`, `DELETE /products/{id}`
- `GET /{locale}/products` (index), `GET /{locale}/products/select`,
  `GET /{locale}/products/{id}/price-breakdown`, `GET /{locale}/products/{id}` (show)

## Controller — not thin, has real logic

`ProductController`:
- `show()` override: appends `collection_ids` (from the `product_collection`
  pivot) onto the parent's cached response.
- `priceBreakdown($locale, $id)`: the canonical implementation of how
  Product/Variant/AttributeOption prices combine into a final per-channel/
  per-currency price, and how a Variant's attribute values override the
  Product's. Full detail: [[catalog-pricing]], [[catalog-attributes]].
  Don't reimplement this combination logic elsewhere — call/extend this.

## Cross-cutting behavior

See [[catalog-channels]] (visibility cascade), [[catalog-pricing]] (its own
+ variant + attribute-option prices), [[catalog-attributes]] (custom
fields), [[catalog-media]] (images/documents), [[catalog-model-variant]]
(children).
