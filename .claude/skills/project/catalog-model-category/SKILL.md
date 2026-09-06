---
name: catalog-model-category
description: Use whenever changing the Category model, its migration, or CategoryController in this Laravel API — Category is a standalone self-nesting tree, NOT connected to Brand/Series/Collection/Product.
---

# Category

## Relations — a standalone, self-nesting tree

```php
public function parent(): BelongsTo
{
    return $this->belongsTo(Category::class, 'parent_id');
}
```

That's the **only** relation. No `children()` inverse is declared. Category
has **no relation to Brand, Series, Collection, or Product** — no
`category_id` on `products`, no pivot table anywhere linking them (confirmed
against every relevant migration). Despite sitting in the middle of the
conceptual "Brand > Series > Collection > Category > Product" ordering
(see [[catalog-hierarchy]]), it is completely disconnected from the rest of
the catalog graph at the FK level — it only nests within itself.

## Traits & properties

`HasTranslations, HasCache, Sluggable, HasAttributes, HasChannelVisibility,
HasMediaCollections` — **no `HasFactory`, no `HasCollections`**.

- `$translatable = ['name', 'slug', 'description', 'short_description']`
  (richer than Brand/Series/Collection, which only have name+slug).
- `$sluggable = 'name'`.
- `#[Fillable(['id', 'parent_id'])]`.
- `ancestorGroupsForVisibility()` **is** overridden:
  ```php
  public function ancestorGroupsForVisibility(): array
  {
      return $this->parent_id ? [[$this->parent]] : [];
  }
  ```
  A subcategory's channel visibility cascades from its parent category only
  (naturally recursing since the parent's own `isVisibleInChannel()` is what
  gets checked) — see [[catalog-channels]].
- `const PARENT_CATEGORY_TRANSLATIONTABLE_ALIAS = 'parent_category_translations'`
  — used by `CategoryQueryBuilder::listExtended()` to self-join the parent's
  translation row (for `parent_name`/`parent_slug` in listings).

## Table

`categories`: `id`, `parent_id` (nullable `foreignId`, no explicit FK
constraint chained), timestamps. `category_translations` adds `name`,
`slug` (unique), `short_description` (255), `description` (500).

## Routes

- `PUT /categories/{id}`, `POST /categories`, `DELETE /categories/{id}`
- `GET /{locale}/categories` (index), `GET /{locale}/categories/select`,
  `GET /{locale}/categories/{id}` (show)

## Controller

`CategoryController` — thin, standard boilerplate. No parent/child
cycle-prevention logic visible in the controller itself — if that's needed,
it belongs in `CategoryRequest`'s validation rules, not here.

## Cross-cutting behavior

See [[catalog-channels]], [[catalog-attributes]], [[catalog-media]]. No
`HasPrices`, no `HasCollections`.
