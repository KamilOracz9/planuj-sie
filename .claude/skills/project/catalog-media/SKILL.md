---
name: catalog-media
description: Use whenever adding/changing media, image, or document handling for a catalog model (Brand, Series, Collection, Category, Product, Variant, Attribute, AttributeOption, AttributeValue) in this Laravel API, or the global gallery/file library — explains the data-driven MediaCollection system, assignments, conversions, and mandates the kamiloracz9/media-gallery package (not the legacy in-app Gallery classes) for the global gallery.
---

# Media & documents

Media is built on Spatie MediaLibrary's `media` table, used in **two
separate systems** distinguished by `model_type`.

## 1. Catalog media — data-driven, per (model type, channel)

Every catalog model (`Product`, `Variant`, `Brand`, `Series`, `Collection`,
`Category`, `Attribute`, `AttributeOption`, `AttributeValue`) uses the single
generic `App\Traits\Media\HasMediaCollections` trait. Collections are **not**
hardcoded per model class — they're rows in the `media_collections` table
(`App\Models\MediaCollection`), each with:

- `kind`: `image` | `document` — drives the accepted MIME types
  (`MediaCollection::MIME_MAP`: images = jpeg/png/webp; documents =
  pdf/doc(x)/xls(x)/ppt(x)/txt/csv).
- `type`: `single` | `multiple` — whether more than one file may exist for a
  given `(model, collection, channel)` triple. **Not** enforced via Spatie's
  `singleFile()` (which isn't channel-aware) — `MediaController::store()`/
  `attach()` manually deletes any pre-existing media in that exact triple
  before adding a new one when `type === 'single'`.

`registerMediaCollections()` loops **every** `MediaCollection` row for
**every** model and registers it with `acceptsMimeTypes()` — so which
collections exist is entirely config-driven, not declared per model class.

**Which collections are offered for upload to which model type, per
channel** is a separate config table: `media_collection_assignments`
(`App\Models\MediaCollectionAssignment`), keyed by
`(media_collection_id, channel_id, model_type)` — `model_type` here is a
plain string key from `config('media.model_types')` (`products`, `variants`,
`brands`, `series`, `collections`, `categories`, `attributes`,
`attribute-options`, `attribute-values`), not a class name. Uploading
without a matching assignment row 422s (`MediaController::ensureAssigned()`).
Media already uploaded stays visible even if its assignment is later
revoked — assignments gate new uploads, not existing files.

**Ordering** is Spatie's own `order_column` on the `media` table, reordered
via `MediaController::reorder()` → `Media::setNewOrder($ids)`.

**Conversions** (thumbnails etc.) are configured per
`(media_collection_id, channel_id, name)` in `media_collection_conversions`
(`App\Models\MediaCollectionConversion`: `width`, `height`,
`fit: crop|contain`), written through `MediaCollection`'s own save via
`App\Traits\HasMediaCollectionConversions`. At upload time,
`HasMediaCollections::registerMediaConversions()` looks up conversions
matching `(media_collection_id, channel_id = $media->channel_id)` — **the
same collection can have different thumbnail sizes per channel.**

**Ordering constraint that matters when writing upload code:** set
`channel_id` via `withProperties(['channel_id' => $channelId])` **before**
`toMediaCollection()` — Spatie generates conversions synchronously inside
that call, and conversion lookup reads `$media->channel_id`. Setting it via
a later `->update()` is too late.

## 2. Gallery — use the `kamiloracz9/media-gallery` package, not the in-app classes

The global, out-of-scope media/document library (not tied to any catalog
entity, organized by folders instead) is **`kamiloracz9/media-gallery`**
(required in `composer.json`, vendored at
`vendor/kamiloracz9/media-gallery`) — `Kamiloracz9\MediaGallery\Models\Gallery`
(singleton via `Gallery::instance()`) + `Kamiloracz9\MediaGallery\Models\MediaFolder`
(self-nesting tree), with config-driven collections
(`config('gallery.collections')`, not a hardcoded `'images'`/`'documents'`
pair) and its own `GalleryController`/`MediaFolderController` generic over a
`{collection}` route parameter. **Use this package for any new global/gallery
work — do not write new code against it.**

`App\Models\Gallery`, `App\Http\Controllers\PanelControllers\BaseGalleryController`/
`GalleryController`/`DocumentController`/`MediaFolderController`, and
`App\Traits\Media\HasDocumentMedia` are the **legacy in-house implementation
this package replaces** — they still exist in `app/` but are superseded, not
a second system to keep building on. Don't add a new file type or a new
`$collection` there; add/extend a `config('gallery.collections')` entry
instead. If you're touching this area for another reason, prefer wiring
the routes/controllers over to the package's own
(`Kamiloracz9\MediaGallery\Http\Controllers\GalleryController`/
`MediaFolderController`) rather than extending the legacy classes further.

A file from the gallery package can still be **copied** into a catalog
model's own `MediaCollection`-based collection via `MediaController::attach()`
below (or directly via Spatie's own `Media::copy()` — see the package's
README) — that bridge is app-specific and stays in `api/`.

## Where things live

**Catalog media (stays in `api/`):**
- `App\Traits\Media\HasMediaCollections`, `HasMediaCollectionAssignments`,
  `HasMediaCollectionConversions`
- `App\Models\MediaCollection`, `MediaCollectionAssignment`,
  `MediaCollectionConversion`
- `App\Http\Controllers\PanelControllers\Media\MediaController` (catalog
  media CRUD/attach/reorder)
- `App\Http\Resources\MediaResource` (`id, collection_name, channel_id,
  name, file_name, mime_type, size, order_column, folder_id, url,
  conversions, created_at`), `MediaCollectionResource` (`id, code, name,
  kind, type`)
- `config/media.php` — the allow-list of valid `{modelType}` route segments

**Gallery (the `kamiloracz9/media-gallery` package):**
- `Kamiloracz9\MediaGallery\Models\Gallery`, `MediaFolder`
- `Kamiloracz9\MediaGallery\Http\Controllers\GalleryController`,
  `MediaFolderController`
- `config/gallery.php` — collections (mime types, max size, conversions),
  route prefix/middleware
- Legacy, superseded: `App\Models\Gallery`, `BaseGalleryController`/
  `GalleryController`/`DocumentController`/`MediaFolderController`,
  `App\Traits\Media\HasDocumentMedia`

## Decision rule

- Adding a new kind of file to a catalog model → add/reuse a
  `MediaCollection` row + an assignment, don't hardcode a new trait or a new
  Spatie collection name in the model class.
- Need it available in the global asset library, unattached to a specific
  catalog record → that's the `kamiloracz9/media-gallery` package. Add/extend
  a `config('gallery.collections')` entry, don't extend the legacy
  `App\Models\Gallery`/`BaseGalleryController` classes.
