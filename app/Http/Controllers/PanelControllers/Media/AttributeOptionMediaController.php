<?php

namespace App\Http\Controllers\PanelControllers\Media;

use App\Models\AttributeOption;

class AttributeOptionMediaController extends BaseMediaController
{
    protected string $modelClass = AttributeOption::class;

    protected array $collections = [
        'icon' => true,
    ];
}
