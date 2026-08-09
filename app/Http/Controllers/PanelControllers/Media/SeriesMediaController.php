<?php

namespace App\Http\Controllers\PanelControllers\Media;

use App\Models\Series;

class SeriesMediaController extends BaseMediaController
{
    protected string $modelClass = Series::class;

    protected array $collections = [
        'logo' => true,
        'documents' => false,
    ];
}
