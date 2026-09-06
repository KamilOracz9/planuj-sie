---
name: catalog-attributes
description: Use whenever adding/changing custom attributes on a catalog model (Brand, Series, Collection, Category, Product, Variant) in this Laravel API — explains AttributeType/Attribute/AttributeOption/AttributeValue and how HasAttributes stores and type-coerces attribute data.
---

# Attributes

## The four models

- **`AttributeType`** — a fixed, closed set of type codes (seeded, not a DB
  enum): `text`, `number`, `select`, `multiselect`, `date`, `boolean`.
  Translatable `name`/`slug`, `order_column`. Read-only reference data — no
  create/update/delete routes, only `index`/`select`, like `Currency`.
- **`Attribute`** — a named field of a given type (e.g. "Color" of type
  `select`): `attribute_type_id` (FK, cascade-delete), translatable
  `name`/`slug`, `order_column`, has media. **No `HasPrices`/
  `HasChannelVisibility`** — an Attribute definition itself isn't priced or
  channel-toggled.
- **`AttributeOption`** — one option of a `select`/`multiselect` `Attribute`
  (e.g. "Red"): `attribute_id`, `order_column`, translatable `name`/`slug`,
  has media, and **has `HasPrices`** — individual options can carry their
  own per-channel/currency price delta (this is what
  [[catalog-pricing]]'s `priceBreakdown()` sums up).
- **`AttributeValue`** — the polymorphic pivot storing **one value per
  `(model, attribute)`**: `morphs('model')` + `attribute_id` (cascade-delete)
  + `data` (JSON) + `order_column`. Effectively unique per
  `(model_type, model_id, attribute_id)` (enforced by `HasAttributes`'
  `updateOrCreate` keying, not a DB constraint). Has media too (e.g. a
  swatch image on the value itself) but no pricing/visibility of its own.

## HasAttributes — used by Product, Variant, Brand, Series, Collection, Category

On `saved()`, reads the request's `attributes` array
(`[{attribute_id, data}]`):

- Submitted rows are `updateOrCreate`d **keyed by `(model_id, model_type,
  attribute_id)`** — deliberately keyed by `attribute_id` rather than
  delete+recreate, so an `AttributeValue` **keeps the same id (and any
  attached media) across edits.**
- Existing values no longer submitted are deleted **one-by-one** (not a mass
  delete) so each `AttributeValue`'s own `deleting` event fires and its
  attached media gets cleaned up too. Same one-by-one approach on the
  model's own `deleted()`.
- `data` is coerced server-side based on the Attribute's type (looked up via
  `App\Http\Repositories\AttributeRepository::getAttributeType()`, cached)
  — **the request/validation layer doesn't need to know the type**, it just
  passes through a raw scalar/array:

  ```php
  'text'        => ['value' => $attribute['data']],
  'number'      => ['value' => is_numeric($attribute['data']) ? $attribute['data'] + 0 : null],
  'boolean'     => ['value' => filter_var($attribute['data'], FILTER_VALIDATE_BOOLEAN)],
  'select'      => ['value' => (int) $attribute['data']],                       // one AttributeOption id
  'multiselect' => ['value' => array_map('intval', (array) $attribute['data'])], // AttributeOption ids
  'date'        => ['value' => $attribute['data']],
  ```

- `attributeValues()` is an ad hoc query for `(model_type, model_id)` rows —
  not a declared Eloquent relation.

## When adding a new attribute-bearing feature

- Don't add a bespoke column for a "custom field" on a catalog model — model
  it as an `Attribute` (+ `AttributeOption`s if it's a choice field) and let
  `HasAttributes` store the value.
- If the new attribute needs its own price delta (like a paint color
  upcharge), that's `AttributeOption`'s `HasPrices` — see
  [[catalog-pricing]] for how it's summed into `priceBreakdown()`.
- Don't invent a new `data` shape per type ad hoc — extend the coercion map
  in `HasAttributes` so every consumer (including `priceBreakdown()`) agrees
  on the shape.

## Where things live

- `App\Models\AttributeType`, `Attribute`, `AttributeOption`, `AttributeValue`
- `App\Traits\HasAttributes`
- `App\Http\Repositories\AttributeRepository`
