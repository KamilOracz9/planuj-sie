---
name: api-security
description: Use whenever adding or changing a route, controller, auth flow, or anything serving media/files in this Laravel API — mandates an API key on every endpoint, JWT auth on every endpoint except login, and private-only storage for all media/files.
---

# API security requirements

Three non-negotiable rules for this API. Two of them are **not** currently
enforced end-to-end — treat the gaps below as required fixes whenever you
touch the affected code, not as the status quo to preserve.

## 1. Every endpoint requires the API key

Enforced globally today via `App\Http\Middleware\ApiKeyMiddleware`, prepended
to the global middleware stack in `bootstrap/app.php`:

```php
$middleware->prepend(HandleCors::class);
$middleware->prepend(ApiKeyMiddleware::class);
```

It checks the `X-API-KEY` header against `config('app.api_key')` and returns
a 401 JSON error on mismatch. Since it's global, this is already true for
every request — **keep it that way**: don't register a second router/kernel
entry point, a route, or a middleware group that bypasses the global
middleware stack. Any new route file/group must still go through
`bootstrap/app.php`'s middleware pipeline.

## 2. Every endpoint requires JWT auth, except login

**Currently NOT enforced — this is a gap to fix, not an existing
guarantee.** `config/auth.php` already has the `api` guard configured
(`driver: jwt`, and it's the default guard), and `AuthController::login()`
already issues a JWT via `tymon/jwt-auth`. But in `routes/api.php`, the
`auth:api` group wrapping every resource route is commented out:

```php
Route::group(['middleware' => 'api'], function () {
    Route::post('login', [AuthController::class, 'login']);

    // Route::group(['middleware' => 'auth:api'], function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        User::routes();
        Brand::routes();
        // ...every other model's routes()...
    // });
});
```

**The rule:** every route except `POST /login` must sit inside an active
`auth:api` middleware group — including `logout`, `refresh`, and every model's
`routes()` static method (this also covers media routes, which are
registered from inside model `routes()` methods, e.g.
`MediaCollection::routes()`). When adding a new route or a new model's
`routes()` method, make sure it's nested inside that group; when you next
touch `routes/api.php`, uncomment/restore the `auth:api` wrapping rather
than leaving it commented out.

## 3. All media/files are private — no public URLs

**Currently NOT true — this is a gap to fix.** Media is stored on the
`public` disk (`config/media-library.php`: `'disk_name' => env('MEDIA_DISK', 'public')`),
which is symlinked (`public/storage -> storage/app/public`) and served with
`visibility: public` in `config/filesystems.php`. `MediaResource` returns
`$this->getUrl()` directly — a plain public URL requiring no auth at all,
completely bypassing rules #1 and #2 above.

**The rule:** media/files must never be reachable via a public, unauthenticated
URL. Concretely:
- Don't use the `public` disk (or any disk with `visibility: public`) for
  media/file storage — use a private local disk (or a private S3-style disk
  without public ACLs) with no `public/storage` symlink exposing it.
- Don't return `$media->getUrl()` (or any other direct-to-disk public URL)
  from an API resource. Serve files through an authenticated route instead
  — a controller action that resolves the media/file server-side and streams
  it back (`Storage::disk(...)->response(...)` or equivalent) or issues a
  short-lived signed URL scoped to the authenticated request. That route is
  a normal endpoint, so rules #1 and #2 already apply to it once JWT
  enforcement (rule #2) is in place.
- This applies to every file-serving path: `GalleryController`,
  `DocumentController` (both extend `BaseGalleryController`), and
  `Media/MediaController`, plus any new one added later.

## Checklist for new/changed endpoints

- [ ] Reachable only through the global middleware stack (API key applies).
- [ ] Wrapped in `auth:api`, unless it's the login endpoint itself.
- [ ] If it returns or serves a file/media URL: the URL/stream requires the
      same auth as any other endpoint — never a bare public-disk URL.
