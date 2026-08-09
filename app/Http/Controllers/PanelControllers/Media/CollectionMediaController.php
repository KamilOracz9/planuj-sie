<?php

namespace App\Http\Controllers\PanelControllers\Media;

use App\Models\Collection;

class CollectionMediaController extends BaseMediaController
{
    protected string $modelClass = Collection::class;

    protected array $collections = [
        'logo' => true,
        'documents' => false,
    ];
}
