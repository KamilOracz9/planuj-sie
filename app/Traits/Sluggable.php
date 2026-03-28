<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait Sluggable
{
    public bool $isSluggable = true;

    protected static function bootSluggable()
    {
        static::saving(function ($model) {
            if (isset($model->translatable) && in_array($model->sluggable, $model->translatable)) {
                request()->merge([
                    'slug' => array_map(fn($item) => Str::slug($item), request()->query($model->sluggable))
                ]);
            } else {
                request()->merge([
                    'slug' => Str::slug($model->sluggable)
                ]);
            }
        });
    }
}
