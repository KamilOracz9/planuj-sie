<?php

namespace App\Support\Media;

use Illuminate\Support\Facades\URL;
use Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator;

/**
 * Media lives on the private 'media' disk (see the api-security skill), so
 * Spatie's default disk-path URL is unreachable. This makes every
 * Media::getUrl() call - including inside kamiloracz9/media-gallery's own
 * MediaResource, which lives in vendor/ and can't be edited here - resolve
 * through the 'media.show' route instead, matching what
 * App\Http\Resources\MediaResource already builds by hand.
 *
 * 'media.show' can't sit behind the usual X-API-KEY/JWT checks (a plain
 * <img src> can't send custom headers), so it's signed instead: the
 * signature itself, verified by the route's 'signed' middleware, is what
 * authorizes the request. Same TTL as a JWT access token (config/jwt.php).
 */
class RouteUrlGenerator extends DefaultUrlGenerator
{
    public function getUrl(): string
    {
        return URL::temporarySignedRoute('media.show', now()->addMinutes(config('jwt.ttl')), [
            'media' => $this->media->id,
            'conversion' => $this->conversion?->getName(),
        ]);
    }
}
