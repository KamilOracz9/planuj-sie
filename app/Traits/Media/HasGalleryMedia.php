<?php

namespace App\Traits\Media;

use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait HasGalleryMedia
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->registerGalleryCollections();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->registerGalleryConversions();
    }

    protected function registerGalleryCollections(): void
    {
        $this->addMediaCollection('gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('main_image')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('main_image_2')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    protected function registerGalleryConversions(): void
    {
        $collections = ['gallery', 'main_image', 'main_image_2'];

        $this->addMediaConversion('mobile')
            ->performOnCollections(...$collections)
            ->fit(Fit::Crop, 480, 640)
            ->nonQueued();

        $this->addMediaConversion('tablet')
            ->performOnCollections(...$collections)
            ->fit(Fit::Crop, 768, 1024)
            ->nonQueued();

        $this->addMediaConversion('desktop')
            ->performOnCollections(...$collections)
            ->fit(Fit::Crop, 1200, 1600)
            ->nonQueued();
    }
}
