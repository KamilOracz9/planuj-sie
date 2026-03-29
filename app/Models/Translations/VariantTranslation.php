<?php

namespace App\Models\Translations;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['product_id', 'variant_id', 'locale', 'name', 'slug', 'description', 'short_description'])]
class VariantTranslation extends BaseModel
{
    const FOREIGN_KEY = 'variant_id';
    public $timestamps = false;
}
