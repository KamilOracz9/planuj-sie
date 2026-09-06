---
name: caching-strategy
description: Use whenever adding or changing caching in this Laravel API — dictates using kamiloracz9/swappable-cache for data/query caching in repositories, and mandates spatie/laravel-responsecache globally for every HTTP response, instead of raw Cache:: calls or ad-hoc solutions.
---

# Caching strategy for this API

This project standardizes on two caching libraries. Do not reach for raw
`Cache::remember()`/`Cache::put()` or a custom caching solution when one of
these fits — pick the one whose job matches the problem.

## kamiloracz9/swappable-cache — data / query caching

Already installed (see `composer.json`, source vendored at
`vendor/kamiloracz9/swappable-cache`). Use it whenever you need to cache the
**result of an expensive query or computation** — Eloquent collections,
aggregates, computed data — with stale-while-revalidate semantics: the cached
value is served instantly while a cheap fingerprint check (run after the
response, via `terminating()`) decides whether to refresh it.

Reach for it when:
- Caching Eloquent models/collections that should rehydrate back into real
  model instances (with relations) rather than plain arrays.
- The freshness check should be cheap (e.g. `MAX(updated_at)`) and doesn't
  need to run on every request — only every `checkInterval` seconds.
- You want a reusable, injectable cache source (implement
  `Kamiloracz9\SwappableCache\Contracts\CacheableSource`) rather than inline
  closures scattered across controllers.

```php
use Kamiloracz9\SwappableCache\Facades\SwappableCache;

$products = SwappableCache::remember(
    key: 'active-products',
    resolve: fn () => Product::where('active', true)->get(),
    fingerprint: fn () => Product::where('active', true)->max('updated_at'),
    checkInterval: 60,
    modelClass: Product::class,
);
```

For reusable sources, implement `CacheableSource` and resolve it via
`app(SwappableCache::class)->get(new ActiveProductsSource())`.

## spatie/laravel-responsecache — all HTTP responses, mandatory

**Not yet installed** — run `composer require spatie/laravel-responsecache`
and publish its config before first use. **All responses in this API are
cached through this package** — it's applied globally (its `CacheResponse`
middleware in the global/`api` middleware stack in `bootstrap/app.php`), not
opted into per route or per controller. Don't wrap a controller's own
response body in `cache()->remember()`/`Cache::remember()` (see
`BaseController::index()/select()/show()` for the legacy pattern this
replaces, per [[project-architecture]]) — that job belongs to this
middleware now, at the HTTP layer, not inside the controller.

By default the package only caches safe, cacheable responses (GET requests,
200 status) and skips the rest — so "all responses" in practice means "every
response that's actually safe to cache", enforced globally instead of
decided ad hoc per endpoint. Anything that must never be cached verbatim
(per-authenticated-user data, anything varying by a header/cookie the cache
key doesn't account for) is excluded via its `CacheProfile`
(`config('responsecache.cache_profile')`) — write/adjust the profile, don't
special-case it by leaving the middleware off some routes.

Cache invalidation on writes is handled by flushing on the relevant model
events (the package supports flushing by tag/regex tied to a model), not by
short TTLs alone.

## How the two layers fit together

These aren't alternatives — they cache different things, at different
layers, and normally both apply to the same request at once:

- `spatie/laravel-responsecache` caches the **finished HTTP response**
  (headers + serialized body) so a repeat request never even reaches the
  application.
- `kamiloracz9/swappable-cache` caches the **underlying query/data** a
  repository resolves (per [[project-architecture]], every repository
  method's return value goes through it) — this is what actually runs
  the first time a given response is built, or after the response cache is
  flushed.

## Decision rule

- Caching a **query/data result** inside a repository, that still needs to
  be processed, transformed, or rehydrated into models → `swappable-cache`.
- The **HTTP response layer itself** → always `spatie/laravel-responsecache`
  (global middleware), for every response that's safe to cache; use its
  `CacheProfile` to exclude what genuinely can't be cached, rather than
  skipping the middleware per route.
- A caching need neither package's config can express → ask before inventing
  a new mechanism.
