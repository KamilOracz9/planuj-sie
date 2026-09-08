<?php

namespace App\Models\Translations;

use App\Traits\HasTableHelpers;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Kamiloracz9\EloquentTranslatable\Translation;

#[Fillable(['collection_id', 'locale', 'name', 'slug'])]
class CollectionTranslation extends Translation
{
    use HasTableHelpers;

    const FOREIGN_KEY = 'collection_id';

    public static function foreignKey(): string
    {
        return self::FOREIGN_KEY;
    }
}
