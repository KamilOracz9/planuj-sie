<?php

namespace App\Models\Translations;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['attribute_type_id', 'locale', 'name', 'slug'])]
class AttributeTypeTranslation extends BaseModel
{
    const FOREIGN_KEY = 'attribute_type_id';
    public $timestamps = false;
}
