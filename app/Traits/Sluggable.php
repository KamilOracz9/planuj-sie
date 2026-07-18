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
                $source = request()->input($model->sluggable) ?? $model->getAttributes()[$model->sluggable] ?? null;
                if ($source !== null) {
                    request()->merge([
                        'slug' => array_map(fn($item) => Str::slug($item), (array) $source),
                        $model->sluggable => (array) $source,
                    ]);
                }
            } else {
                $source = request()->input($model->sluggable) ?? $model->getAttributes()[$model->sluggable] ?? null;
                if ($source !== null) {
                    request()->merge([
                        'slug' => Str::slug($source),
                        $model->sluggable => $source,
                    ]);
                }
            }
        });
    }
}
