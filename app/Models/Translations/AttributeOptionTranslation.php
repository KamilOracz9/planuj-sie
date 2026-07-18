<?php

namespace App\Models\Translations;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['attribute_option_id', 'locale', 'name', 'slug'])]
class AttributeOptionTranslation extends BaseModel
{
    const FOREIGN_KEY = 'attribute_option_id';
    public $timestamps = false;
}
