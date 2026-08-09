<?php

namespace App\Traits\Media;

use App\Models\MediaCollection;
use App\Models\MediaCollectionConversion;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

// Replaces HasGalleryMedia/HasIconMedia/HasLogoMedia/HasDocumentMedia:
// collections are now data-driven (App\Models\MediaCollection) instead of
// hardcoded per model class, so every entity that has media uses this one
// generic trait instead of picking from four fixed combinations.
trait HasMediaCollections
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        foreach (MediaCollection::registry() as $mediaCollection) {
            $this->addMediaCollection($mediaCollection['code'])
                // Never ->singleFile(): Spatie's singleFile() is scoped to
                // (model, collection), not (model, collection, channel), so it
                // would delete an existing file for channel A when uploading
                // for channel B. "single vs multiple" per media_collections.type
                // is enforced in App\Http\Controllers\PanelControllers\Media\MediaController,
                // scoped to (model, collection, channel) instead.
                ->acceptsMimeTypes(MediaCollection::MIME_MAP[$mediaCollection['kind']]);
        }
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        if (!$media) {
            return;
        }

        $mediaCollection = collect(MediaCollection::registry())
            ->firstWhere('code', $media->collection_name);

        if (!$mediaCollection) {
            return;
        }

        // Scoped to this specific media's own channel - the only way to get
        // genuinely different conversion dimensions per channel for the same
        // collection, since Spatie's own conversion pipeline has no
        // channel-awareness beyond the $media instance passed in here.
        $conversions = MediaCollectionConversion::query()
            ->where('media_collection_id', $mediaCollection['id'])
            ->where('channel_id', $media->channel_id)
            ->get();

        foreach ($conversions as $conversion) {
            $this->addMediaConversion($conversion->name)
                ->performOnCollections($media->collection_name)
                ->fit($conversion->fit === 'crop' ? Fit::Crop : Fit::Contain, $conversion->width, $conversion->height)
                ->nonQueued();
        }
    }
}
