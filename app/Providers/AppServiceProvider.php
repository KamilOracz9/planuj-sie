<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;
use Spatie\ResponseCache\Facades\ResponseCache;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // kamiloracz9/media-gallery's move()/destroy() actions query and
        // save/delete media through the raw Spatie\...\Media class (a
        // hardcoded import in vendor/, not config('media-library.media_model')),
        // so App\Models\Media's HasCache trait never fires for them - a
        // moved/deleted gallery file wouldn't flush the stale cached listing.
        // Model events are registered per exact class, so this is a separate
        // listener from that one, not a duplicate of it.
        SpatieMedia::saved(fn () => ResponseCache::clear());
        SpatieMedia::deleted(fn () => ResponseCache::clear());
    }
}
