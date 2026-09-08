<?php

namespace App\Models\Translations;

use App\Traits\HasTableHelpers;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Kamiloracz9\EloquentTranslatable\Translation;

#[Fillable(['brand_id', 'locale', 'name', 'slug'])]
class BrandTranslation extends Translation
{
    use HasTableHelpers;

    const FOREIGN_KEY = 'brand_id';

    public static function foreignKey(): string
    {
        return self::FOREIGN_KEY;
    }
}
