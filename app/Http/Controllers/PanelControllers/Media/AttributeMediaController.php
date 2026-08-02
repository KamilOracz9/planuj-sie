<?php

namespace App\Http\Controllers\PanelControllers\Media;

use App\Models\Attribute;

class AttributeMediaController extends BaseMediaController
{
    protected string $modelClass = Attribute::class;

    protected array $collections = [
        'icon' => true,
    ];
}
