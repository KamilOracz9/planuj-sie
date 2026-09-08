<?php

namespace App\Models\Translations;

use App\Traits\HasTableHelpers;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Kamiloracz9\EloquentTranslatable\Translation;

#[Fillable(['attribute_id', 'locale', 'name', 'slug'])]
class AttributeTranslation extends Translation
{
    use HasTableHelpers;

    const FOREIGN_KEY = 'attribute_id';

    public static function foreignKey(): string
    {
        return self::FOREIGN_KEY;
    }
}
