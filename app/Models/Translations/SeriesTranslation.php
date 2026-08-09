<?php

namespace App\Models\Translations;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['series_id', 'locale', 'name', 'slug'])]
class SeriesTranslation extends BaseModel
{
    const FOREIGN_KEY = 'series_id';
    public $timestamps = false;
}
