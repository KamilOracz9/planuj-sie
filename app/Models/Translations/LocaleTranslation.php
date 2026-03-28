<?php

namespace App\Models\Translations;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['locale_id', 'locale', 'name'])]
class LocaleTranslation extends BaseModel
{
    const FOREIGN_KEY = 'locale_id';
    public $timestamps = false;
}
