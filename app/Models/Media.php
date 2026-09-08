<?php

namespace App\Models;

use App\Traits\HasCache;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

/**
 * Swapped in via config('media-library.media_model') so every media
 * upload/delete - including through kamiloracz9/media-gallery's own
 * controllers, which we can't add the trait to directly since they're in
 * vendor/ - flushes the response cache like every other write in this app
 * (see the caching-strategy skill). Without this, a newly uploaded file
 * doesn't show up in the panel until the cached gallery listing expires on
 * its own.
 */
class Media extends SpatieMedia
{
    use HasCache;

    protected static function boot()
    {
        parent::boot();

        static::bootCache();
    }

    private static function clearCache($model = null)
    {
        //
    }
}
