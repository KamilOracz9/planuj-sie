<?php

namespace App\Http\Controllers\PanelControllers\Media;

use App\Models\Brand;

class BrandMediaController extends BaseMediaController
{
    protected string $modelClass = Brand::class;

    protected array $collections = [
        'logo' => true,
        'documents' => false,
    ];
}
