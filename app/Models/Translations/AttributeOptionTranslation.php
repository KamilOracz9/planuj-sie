<?php

namespace App\Models\Translations;

use App\Traits\HasTableHelpers;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Kamiloracz9\EloquentTranslatable\Translation;

#[Fillable(['attribute_option_id', 'locale', 'name', 'slug'])]
class AttributeOptionTranslation extends Translation
{
    use HasTableHelpers;

    const FOREIGN_KEY = 'attribute_option_id';

    public static function foreignKey(): string
    {
        return self::FOREIGN_KEY;
    }
}
