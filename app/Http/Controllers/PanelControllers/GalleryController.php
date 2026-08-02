<?php

namespace App\Http\Controllers\PanelControllers;

class GalleryController extends BaseGalleryController
{
    protected string $collection = 'images';

    protected array $fileRules = ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'];
}
