---
name: catalog-pricing
description: Use whenever adding/changing pricing for Product, Variant, or AttributeOption in this Laravel API — explains HasPrices, the Price model's (channel, currency) scoping, minor-unit storage via Currency, and the priceBreakdown() final-price computation.
---

# Pricing

## HasPrices — used by Product, Variant, and AttributeOption

`App\Traits\HasPrices`: on `saved()`, reads the request's `prices` array
(`[{channel_id, currency_id, amount}]`). A price row is scoped to the
4-tuple `(model_type, model_id, channel_id, currency_id)` — that's the
composite unique key on the `prices` table. `channel_id`/`currency_id` FKs
are `restrictOnDelete` (unlike `ChannelVisibility`'s cascade delete) — you
can't delete a Channel/Currency that still has prices referencing it.

Submitted rows are `updateOrCreate`d; pairs no longer submitted are deleted
(found in PHP since the composite key can't use `whereNotIn` directly).

**Amounts are stored as integers in the currency's minor unit** (e.g. cents),
not decimals. The admin submits major units (e.g. `"19.99"`);
`HasPrices` converts via `$currency->toMinorUnits($amount)` using each row's
own `currency_id` before writing.

## Currency — the single source of unit-conversion logic

`App\Models\Currency`: `code, name, symbol, decimal_places`. Not
translatable (reference data). Provides:
- `toMinorUnits($major) = round($major * 10**decimal_places)`
- `toMajorUnits($minor) = round($minor / 10**decimal_places, decimal_places)`

Both `HasPrices` (write) and `PriceController`/`ProductController::priceBreakdown()`
(read) go through these — don't reimplement the conversion elsewhere.

## Price — no CRUD routes of its own

`App\Models\Price`: `channel_id, currency_id, model_id, model_type, amount`.
Written only through the owning model's save (via `HasPrices`). Its only
route is `GET .../prices/select/{modelType}/{modelId}`
(`PriceController::selectByModel`), which converts back to major units for
display.

## Variant and AttributeOption have their own independent prices

A Variant does **not** inherit Product's prices by relation — it has its own
`HasPrices` rows (`model_type = Variant::class`). Same for `AttributeOption`
(option-level price deltas for `select`/`multiselect` attributes, see
[[catalog-attributes]]). The relationship between them is additive, computed
at read time by `priceBreakdown()` below — not a DB-level inheritance.

## `ProductController::priceBreakdown()` — the final-price computation

Route: `GET /{locale}/products/{id}/price-breakdown?channel_id=`. Computes,
for each currency the Product has a price in, and for each Variant:

```
final_price = product_price + variant_price + attributes_price
```

- `attributes_price` only comes from `select`/`multiselect` attribute
  values, since `AttributeOption` is the only attribute-related model with
  `HasPrices`.
- A `select` value is one fixed option (one combination). A `multiselect`
  value is a set of *alternatives* the shopper picks **one** of — it fans
  out into one combination per option (never summed within a single
  multiselect), combined cartesian-product-style across different
  attributes via a private `combinationsOf()` helper.
- **Variant attribute values override the Product's for the same
  `attribute_id`** (`array_replace($productAttributeOptions,
  $variantAttributeOptions)`) — attributes the variant doesn't override are
  inherited from the product. See [[catalog-attributes]] for how
  `AttributeValue` rows are keyed.

This is an uncached, single-product admin diagnostic view — not something to
call in a hot list path.

## Where things live

- `App\Traits\HasPrices`
- `App\Models\Price`, `Currency`
- `App\Http\Controllers\PanelControllers\PriceController`,
  `ProductController::priceBreakdown()`
