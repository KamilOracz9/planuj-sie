<?php

namespace App\Http\Controllers\PanelControllers\Media;

use App\Models\Product;

class ProductMediaController extends BaseMediaController
{
    protected string $modelClass = Product::class;

    protected array $collections = [
        'gallery' => false,
        'main_image' => true,
        'main_image_2' => true,
        'documents' => false,
    ];
}
