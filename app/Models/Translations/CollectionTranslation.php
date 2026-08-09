<?php

namespace App\Models\Translations;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['collection_id', 'locale', 'name', 'slug'])]
class CollectionTranslation extends BaseModel
{
    const FOREIGN_KEY = 'collection_id';
    public $timestamps = false;
}
