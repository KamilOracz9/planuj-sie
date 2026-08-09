<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;

// No routes()/queryBuilder() of its own: written only through MediaCollection's
// own save, via HasMediaCollectionAssignments - mirrors MediaCollectionConversion.
#[Fillable(['id', 'media_collection_id', 'channel_id', 'model_type'])]
class MediaCollectionAssignment extends BaseModel
{
}
