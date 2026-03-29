<?php

namespace App\Models\Translations;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['product_id', 'category_id', 'locale', 'name', 'slug', 'description', 'short_description'])]
class ProductTranslation extends BaseModel
{
    const FOREIGN_KEY = 'product_id';
    public $timestamps = false;
}
