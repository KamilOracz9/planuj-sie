<?php

namespace App\Models\Translations;

use App\Traits\HasTableHelpers;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Kamiloracz9\EloquentTranslatable\Translation;

#[Fillable(['series_id', 'locale', 'name', 'slug'])]
class SeriesTranslation extends Translation
{
    use HasTableHelpers;

    const FOREIGN_KEY = 'series_id';

    public static function foreignKey(): string
    {
        return self::FOREIGN_KEY;
    }
}
