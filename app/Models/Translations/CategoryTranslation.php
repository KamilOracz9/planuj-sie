<?php

namespace App\Models\Translations;

use App\Traits\HasTableHelpers;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Kamiloracz9\EloquentTranslatable\Translation;

#[Fillable(['category_id', 'locale', 'name', 'slug', 'description', 'short_description'])]
class CategoryTranslation extends Translation
{
    use HasTableHelpers;

    const FOREIGN_KEY = 'category_id';

    public static function foreignKey(): string
    {
        return self::FOREIGN_KEY;
    }
}
