<?php

namespace App\Models\Translations;

use App\Traits\HasTableHelpers;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Kamiloracz9\EloquentTranslatable\Translation;

#[Fillable(['locale_id', 'locale', 'name'])]
class LocaleTranslation extends Translation
{
    use HasTableHelpers;

    const FOREIGN_KEY = 'locale_id';

    public static function foreignKey(): string
    {
        return self::FOREIGN_KEY;
    }
}
