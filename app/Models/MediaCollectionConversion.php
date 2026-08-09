<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;

// No routes()/queryBuilder(): rows are written exclusively through
// MediaCollection's own save, via the HasMediaCollectionConversions trait -
// mirrors how Price/ChannelVisibility are written through their owning
// entity rather than exposing their own CRUD.
#[Fillable(['id', 'media_collection_id', 'channel_id', 'name', 'width', 'height', 'fit'])]
class MediaCollectionConversion extends BaseModel
{
}
