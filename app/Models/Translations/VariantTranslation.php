<?php

namespace App\Models\Translations;

use App\Traits\HasTableHelpers;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Kamiloracz9\EloquentTranslatable\Translation;

#[Fillable(['product_id', 'variant_id', 'locale', 'name', 'slug', 'description', 'short_description'])]
class VariantTranslation extends Translation
{
    use HasTableHelpers;

    const FOREIGN_KEY = 'variant_id';

    public static function foreignKey(): string
    {
        return self::FOREIGN_KEY;
    }
}
