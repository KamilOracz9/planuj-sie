<?php

namespace App\Http\Controllers\PanelControllers\Media;

use App\Models\Variant;

class VariantMediaController extends BaseMediaController
{
    protected string $modelClass = Variant::class;

    protected array $collections = [
        'gallery' => false,
        'main_image' => true,
        'main_image_2' => true,
        'documents' => false,
    ];
}
