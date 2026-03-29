<?php

namespace App\Models\Translations;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['category_id', 'locale', 'name', 'slug', 'description', 'short_description'])]
class CategoryTranslation extends BaseModel
{
    const FOREIGN_KEY = 'category_id';
    public $timestamps = false;
}
