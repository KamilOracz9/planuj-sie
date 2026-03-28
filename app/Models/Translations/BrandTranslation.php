<?php

namespace App\Models\Translations;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['brand_id', 'locale', 'name', 'slug'])]
class BrandTranslation extends BaseModel
{
    const FOREIGN_KEY = 'brand_id';
    public $timestamps = false;
}
