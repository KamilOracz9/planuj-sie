<?php

namespace App\Models\Translations;

use App\Traits\HasTableHelpers;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Kamiloracz9\EloquentTranslatable\Translation;

#[Fillable(['attribute_type_id', 'locale', 'name', 'slug'])]
class AttributeTypeTranslation extends Translation
{
    use HasTableHelpers;

    const FOREIGN_KEY = 'attribute_type_id';

    public static function foreignKey(): string
    {
        return self::FOREIGN_KEY;
    }
}
