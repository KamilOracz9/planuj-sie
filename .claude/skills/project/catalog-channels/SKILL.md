---
name: catalog-channels
description: Use whenever adding/changing channel-scoped behavior or per-channel visibility for a catalog model (Brand, Series, Collection, Category, Product, Variant) in this Laravel API — explains Channel, ChannelVisibility, and the HasChannelVisibility cascade (isEnabledForChannel vs isVisibleInChannel vs the admin list filter).
---

# Channels & visibility

## Channel

`App\Models\Channel`: `is_default` (bool) + translatable `name`/`slug`.
`boot()` enforces exactly one default: on `saved()`, if `is_default` is
true, every other channel is mass-updated to `is_default = false` (a mass
update, deliberately not re-triggering the same `saved` event).

## ChannelVisibility — a per-model, per-channel on/off toggle

`App\Models\ChannelVisibility`: polymorphic row (`channel_id, model_id,
model_type, is_enabled`), unique on `(channel_id, model_type, model_id)`.
It has **no CRUD routes of its own** — rows are written only through the
owning entity's save, via `App\Traits\HasChannelVisibility`, used by
`Product`, `Variant`, `Brand`, `Series`, `Collection`, `Category`. Its only
routes are read-only: `GET .../channel-visibilities/select/{modelType}/{modelId}`
(raw per-channel rows, for an edit form) and
`GET .../channel-visibilities/report/{modelType}/{modelId}/{channelId}`
(the computed cascade explanation, see `visibilityReport()` below).

**Important: this is opt-out, not opt-in.** `isEnabledForChannel(int $channelId): bool`
looks up this model's own row for that channel — **if no row exists, it's
visible by default.**

## The cascade — `ancestorGroupsForVisibility()` / `isVisibleInChannel()`

`ancestorGroupsForVisibility(): array` defaults to `[]` (no cascade) and is
overridden per model to declare which other models this one's visibility
depends on. Shape: **outer array = dimensions ANDed together; inner array =
candidates ORed within a dimension.** An empty inner group (no ancestor in
that dimension) is skipped, not blocking.

`isVisibleInChannel(int $channelId): bool`: true only if this model's own
`isEnabledForChannel()` is true **and**, for every ancestor dimension, **at
least one** candidate in that group is itself `isVisibleInChannel()`
(recursive).

Overrides found in the catalog models:

| Model | `ancestorGroupsForVisibility()` |
|---|---|
| `Product` | `[[brand]], [[series]], [...collections]` (each only if set/non-empty) — visible only if not blocked by its own toggle **and** (no brand or brand visible) **and** (no series or series visible) **and** (no collections or at least one attached collection visible) |
| `Variant` | `[[product]]` — depends only on its parent Product |
| `Category` | `[[parent]]` if `parent_id` set, else `[]` — cascades up the category tree |
| `Brand`, `Series`, `Collection` | not overridden → `[]`, top-level, no cascade |

`visibilityReport()` runs the same logic non-short-circuiting, returning
`{channel_id, own_enabled, blocking_groups: [...], visible}` for the admin
diagnostics endpoint (`ChannelVisibilityController::report()`).

## Two different "is this visible" checks — don't confuse them

- **`BaseQueryBuilder::filterByChannel()`** — the **admin list filter**.
  Excludes only rows explicitly disabled **at their own level**
  (`channel_visibilities.is_enabled = false` for that exact model),
  matching `isEnabledForChannel()`'s own default-visible fallback.
  Deliberately does **not** replicate the ancestor cascade: "a Product
  should stay visible/manageable even if its Brand happens to be hidden."
  A safe no-op for models with no `channel_visibilities` rows at all.
- **`isVisibleInChannel()`** — the **full cascade**, for storefront
  rendering, where a Product genuinely shouldn't show if its Brand is
  hidden.

When writing a new admin listing, use `filterByChannel()`. When writing
anything that decides what a shopper actually sees, use
`isVisibleInChannel()`. Don't mix them up.

## Where things live

- `App\Models\Channel`, `ChannelVisibility`
- `App\Traits\HasChannelVisibility`
- `App\QueryBuilders\BaseQueryBuilder::filterByChannel()`
- `App\Http\Controllers\PanelControllers\ChannelVisibilityController`
