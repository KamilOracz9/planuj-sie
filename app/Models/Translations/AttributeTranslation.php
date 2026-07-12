<?php

namespace App\Models\Translations;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['attribute_id', 'locale', 'name', 'slug'])]
class AttributeTranslation extends BaseModel
{
    const FOREIGN_KEY = 'attribute_id';
    public $timestamps = false;
}
