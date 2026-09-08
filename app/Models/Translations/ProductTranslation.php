<?php

namespace App\Models\Translations;

use App\Traits\HasTableHelpers;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Kamiloracz9\EloquentTranslatable\Translation;

#[Fillable(['product_id', 'category_id', 'locale', 'name', 'slug', 'description', 'short_description'])]
class ProductTranslation extends Translation
{
    use HasTableHelpers;

    const FOREIGN_KEY = 'product_id';

    public static function foreignKey(): string
    {
        return self::FOREIGN_KEY;
    }
}
