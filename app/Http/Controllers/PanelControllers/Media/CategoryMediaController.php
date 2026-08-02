<?php

namespace App\Http\Controllers\PanelControllers\Media;

use App\Models\Category;

class CategoryMediaController extends BaseMediaController
{
    protected string $modelClass = Category::class;

    protected array $collections = [
        'icon' => true,
        'documents' => false,
    ];
}
