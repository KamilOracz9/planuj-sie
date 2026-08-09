<?php

namespace App\Traits\Media;

/**
 * Adds a 'documents' collection to a model. Always combined with another
 * Has*Media trait (which provides InteractsWithMedia + its own collections),
 * since this only contributes a collection, not a full HasMedia setup.
 *
 * Only App\Models\Gallery (the out-of-scope global media library) uses this
 * now - the 9 catalog entities (Product, Brand, etc.) were migrated to the
 * generic App\Traits\Media\HasMediaCollections during the media rework and
 * no longer reference it.
 */
trait HasDocumentMedia
{
    protected function registerDocumentCollection(): void
    {
        $this->addMediaCollection('documents')
            ->acceptsMimeTypes([
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'text/plain',
                'text/csv',
            ]);
    }
}
