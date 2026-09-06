---
name: translation-strategy
description: Use whenever adding or changing per-locale/translatable attributes on an Eloquent model in this Laravel API — dictates using kamiloracz9/eloquent-translatable (HasTranslations + Sluggable + query-builder macros), not spatie/laravel-translatable and not a new custom trait.
---

# Translation strategy for this API

This project standardizes on **`kamiloracz9/eloquent-translatable`**
(installed, see `composer.json` and `vendor/kamiloracz9/eloquent-translatable`)
for any model attribute that needs a per-locale value (name, slug,
description, ...). It replaces the old in-house `App\Traits\HasTranslations`
/ `App\Traits\Sluggable` / `BaseQueryBuilder::withTranslation()` mechanism —
new translatable models and fields should be built on this package, not by
copying the legacy in-app traits.

`spatie/laravel-translatable` is also required in `composer.json` but is
**dead weight — nothing in `app/` uses it**. Do not reach for it; do not
introduce new usages of it. If you notice it's genuinely unused when you're
already touching `composer.json`, flag it rather than building on it.

## What the package gives you

- `Kamiloracz9\EloquentTranslatable\HasTranslations` — trait for the host
  model (e.g. `Product`). Persists per-locale attribute values into a
  dedicated `{model}_translations` table on save, deletes them on delete,
  and exposes `translations()` / `translation($locale = null)` relations.
- `Kamiloracz9\EloquentTranslatable\Translation` — abstract base class for
  the translation model (e.g. `ProductTranslation`), implementing
  `Kamiloracz9\EloquentTranslatable\Contracts\TranslationModel`.
- `Kamiloracz9\EloquentTranslatable\Sluggable` — optional trait that
  auto-derives a `slug` (scalar or per-locale array) from another attribute.
- `withTranslation()` / `withTranslations()` — macros on both
  `Illuminate\Database\Query\Builder` and `Illuminate\Database\Eloquent\Builder`
  for left-joining translation rows (with NULL-locale fallback) into list
  queries.

Full usage examples, the migration pattern (`{model}` +
`{model}_translations` tables with a `(slug, locale)` composed-unique key —
**not** a globally-unique slug), and config (`config('translatable.locale_column')`)
are documented in the package's own README (vendor/kamiloracz9/eloquent-translatable/README.md
or the source repo).

## How to add a new translatable model

1. Migration: create the model's table plus a `{model}_translations` sibling
   table (see package README for the exact shape).
2. Translation model: `class XTranslation extends Translation` with
   `foreignKey(): string` returning the FK column name.
3. Host model: `use HasTranslations, Sluggable;`, declare
   `public array $translatable = [...]`, implement
   `translationModel(): string`, and call `static::bootTranslations()`
   (+ `static::bootSluggable()` if used) from `boot()`.
4. List/edit queries: join via `->withTranslation(XTranslation::class, $locale, 'x_id')`
   (single locale) or `->withTranslations(...)` (all locales, e.g. for an
   edit form) instead of hand-rolled joins.

## Decision rule

- A model attribute that varies by locale → `kamiloracz9/eloquent-translatable`
  (`HasTranslations` + a `Translation` subclass).
- A slug derived from a translatable attribute → the package's `Sluggable`
  trait, not a bespoke slugify-on-save hook.
- Neither fits (e.g. genuinely global i18n strings/UI copy, not per-record
  data) → that's Laravel's own localization (`lang/`), not this package —
  ask before inventing something else.
