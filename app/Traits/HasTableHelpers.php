<?php

namespace App\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Shared by App\Models\BaseModel and every App\Models\Translations\*
 * class (which can't extend BaseModel - they extend
 * Kamiloracz9\EloquentTranslatable\Translation instead) so both still get
 * the same table/column-name helpers used throughout query builders,
 * controllers, and requests.
 */
trait HasTableHelpers
{
    public static function tableName(): string
    {
        return (new static)->getTable();
    }

    public static function columnName(string $column): string
    {
        return self::tableName().'.'.$column;
    }

    public static function modelName(): string
    {
        return Str::snake(Arr::last(explode('\\', get_called_class())));
    }
}
