<?php

namespace App\Models;

use App\Traits\Media\HasDocumentMedia;
use Illuminate\Support\Facades\Route;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Gallery extends BaseModel implements HasMedia
{
    use InteractsWithMedia, HasDocumentMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->registerDocumentCollection();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->performOnCollections('images')
            ->fit(Fit::Crop, 300, 300)
            ->nonQueued();
    }

    /**
     * All gallery images and documents are attached to this single, always-present row.
     */
    public static function instance(): self
    {
        return static::query()->firstOrCreate([]);
    }

    public static function routes()
    {
        return [
            Route::group(['prefix' => 'gallery'], function () {
                Route::get('/', [\App\Http\Controllers\PanelControllers\GalleryController::class, 'index']);
                Route::post('/', [\App\Http\Controllers\PanelControllers\GalleryController::class, 'store']);
                Route::delete('/{mediaId}', [\App\Http\Controllers\PanelControllers\GalleryController::class, 'destroy']);
            }),
            Route::group(['prefix' => 'documents'], function () {
                Route::get('/', [\App\Http\Controllers\PanelControllers\DocumentController::class, 'index']);
                Route::post('/', [\App\Http\Controllers\PanelControllers\DocumentController::class, 'store']);
                Route::delete('/{mediaId}', [\App\Http\Controllers\PanelControllers\DocumentController::class, 'destroy']);
            }),
        ];
    }
}
