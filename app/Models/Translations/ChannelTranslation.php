<?php

namespace App\Models\Translations;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['channel_id', 'locale', 'name', 'slug'])]
class ChannelTranslation extends BaseModel
{
    const FOREIGN_KEY = 'channel_id';
    public $timestamps = false;
}
