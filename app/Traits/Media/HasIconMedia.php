<?php

namespace App\Traits\Media;

use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait HasIconMedia
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->registerIconCollection();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->registerIconConversions();
    }

    protected function registerIconCollection(): void
    {
        $this->addMediaCollection('icon')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    protected function registerIconConversions(): void
    {
        $this->addMediaConversion('thumb')
            ->performOnCollections('icon')
            ->fit(Fit::Crop, 50, 50)
            ->nonQueued();
    }
}
