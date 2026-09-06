---
name: catalog-hierarchy
description: Use whenever working with the catalog organization models (Brand, Series, Collection, Category, Product, Variant) in this Laravel API — the entry point for how they actually relate to each other (it is NOT a strict parent/child chain). Points to catalog-model-*, catalog-media, catalog-channels, catalog-pricing, catalog-attributes for detail on each.
---

# Catalog hierarchy — how it actually fits together

"Brand > Series > Collection > Category > Product > Variant" describes the
six catalog organization concepts in this app, but **it is not a real
foreign-key chain**. Don't assume a strict parent/child relationship between
them — check the actual relation before writing a query or a migration.

## The real graph

```
Brand ─┐
Series ┼─ all optional, independent, parallel ─→ Product ──hasMany──→ Variant
Collection ┘  (belongsToMany, via product_collection)     (belongsTo, required, cascade-delete)

Category
  └─ self-referencing tree only (parent_id) — NOT connected to
     Brand, Series, Collection, or Product at all
```

- **Product** `belongsTo` `Brand` (nullable `brand_id`), `belongsTo` `Series`
  (nullable `series_id`), and `belongsToMany` `Collection` (via the
  `product_collection` pivot, managed by `App\Traits\HasCollections`, which
  only `Product` uses). These three groupings are **independent and all
  optional** — a Product can have any combination of them, not a required
  chain through all three.
- **Category** has exactly one relation: `parent(): belongsTo(Category::class, 'parent_id')`
  — a self-nesting tree, with no `children()` inverse declared. It has **no
  relation to Product, Brand, Series, or Collection** — no `category_id` on
  `products`, no pivot table. Despite sitting in the middle of the
  conceptual ordering, it is a completely separate, standalone tree.
- **Variant** `belongsTo` `Product` (`product_id` NOT NULL,
  `cascadeOnDelete()`) — the *only* mandatory, strict parent/child link in
  this whole set. A Variant has no relation to Brand/Series/Collection/
  Category directly; reach those only by traversing `variant->product->brand`
  etc.

## What's shared vs. what's specific

`Brand`, `Series`, `Collection`, `Category`, `Product`, `Variant` all lean on
the same generic, polymorphic capabilities (traits, not hierarchy):

| Trait | Brand | Series | Collection | Category | Product | Variant |
|---|---|---|---|---|---|---|
| `HasTranslations` (+ `Sluggable`) | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `HasChannelVisibility` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `HasAttributes` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `HasMediaCollections` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `HasCollections` (member of Collections) | | | | | ✓ | |
| `HasPrices` | | | | | ✓ | ✓ |

See: [[catalog-channels]] for `HasChannelVisibility`, [[catalog-attributes]]
for `HasAttributes`, [[catalog-media]] for `HasMediaCollections`,
[[catalog-pricing]] for `HasPrices`.

Every base table (`brands`, `series`, `collections`, `products`, `variants`)
is essentially `id` + FK columns + timestamps — **all human-facing fields
(name, slug, description, short_description) live in a sibling
`{model}_translations` table**, per [[translation-strategy]]. `Category` is
the one exception with a real scalar column of its own (`parent_id`).

## Per-model detail

Each model has its own skill with relations, table columns, routes, and
controller notes: [[catalog-model-brand]], [[catalog-model-series]],
[[catalog-model-collection]], [[catalog-model-category]],
[[catalog-model-product]], [[catalog-model-variant]].
