---
name: catalog-model-series
description: Use whenever changing the Series model, its migration, or SeriesController in this Laravel API.
---

# Series

## Relations

None — same shape as [[catalog-model-brand]]. `Product::series()` holds the
(optional) inverse `belongsTo`; Series itself declares no relation back.
`protected $table = 'series';` is set explicitly, since Eloquent's default
pluralization of "Series" would otherwise misbehave.

## Traits & properties

Identical set to Brand: `HasTranslations, HasCache, Sluggable, HasFactory,
HasAttributes, HasChannelVisibility, HasMediaCollections`.

- `$translatable = ['name', 'slug']`, `$sluggable = 'name'`,
  `#[Fillable(['id'])]`.
- No `ancestorGroupsForVisibility()` override → `[]` (see [[catalog-channels]]).

## Table

`series`: only `id`, timestamps. `series_translations` holds `name`/`slug`.

## Routes

- `PUT /series/{id}`, `POST /series`, `DELETE /series/{id}`
- `GET /{locale}/series` (index), `GET /{locale}/series/select`,
  `GET /{locale}/series/{id}` (show)

## Controller

`SeriesController` — thin, same boilerplate pattern as `BrandController`.

## Cross-cutting behavior

See [[catalog-channels]], [[catalog-attributes]], [[catalog-media]]. No
`HasPrices`.
