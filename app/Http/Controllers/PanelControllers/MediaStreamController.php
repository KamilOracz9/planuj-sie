<?php

namespace App\Http\Controllers\PanelControllers;

use App\Http\Controllers\Controller;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Streams a media file (or one of its named conversions) from the private
 * media disk. Media is no longer stored on the public disk (see the
 * api-security skill) - this authenticated route is the only way to fetch
 * a file's bytes; MediaResource's `url`/`conversions` fields point here.
 */
class MediaStreamController extends Controller
{
    public function show(int $media, ?string $conversion = null)
    {
        $media = Media::query()->findOrFail($media);

        $path = $conversion ? $media->getPath($conversion) : $media->getPath();

        abort_unless(is_file($path), 404);

        return response()->file($path, [
            'Content-Type' => $media->mime_type,
        ]);
    }
}
