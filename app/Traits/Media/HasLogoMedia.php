<?php

namespace App\Traits\Media;

use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait HasLogoMedia
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->registerLogoCollection();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->registerLogoConversions();
    }

    protected function registerLogoCollection(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    protected function registerLogoConversions(): void
    {
        $this->addMediaConversion('thumb')
            ->performOnCollections('logo')
            ->fit(Fit::Contain, 300, 300)
            ->nonQueued();
    }
}
