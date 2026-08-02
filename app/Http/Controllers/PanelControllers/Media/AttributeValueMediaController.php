<?php

namespace App\Http\Controllers\PanelControllers\Media;

use App\Models\AttributeValue;

class AttributeValueMediaController extends BaseMediaController
{
    protected string $modelClass = AttributeValue::class;

    protected array $collections = [
        'icon' => true,
    ];
}
