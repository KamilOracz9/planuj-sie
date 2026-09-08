<?php

namespace App\Models\Translations;

use App\Traits\HasTableHelpers;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Kamiloracz9\EloquentTranslatable\Translation;

#[Fillable(['channel_id', 'locale', 'name', 'slug'])]
class ChannelTranslation extends Translation
{
    use HasTableHelpers;

    const FOREIGN_KEY = 'channel_id';

    public static function foreignKey(): string
    {
        return self::FOREIGN_KEY;
    }
}
