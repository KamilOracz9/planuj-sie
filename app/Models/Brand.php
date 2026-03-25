<?php

namespace App\Models;

use App\Enums\CacheKeys;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

#[Fillable(['name', 'slug'])]
class Brand extends BaseModel
{
    use HasTranslations;

    const LIST_FIELDS = ['id', 'name', 'slug'];

    protected array $translatable = ['name', 'slug'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($brand) {
            $brand->setTranslations('slug', array_map(fn($item) => Str::slug($item), $brand->getTranslations('name')));

            cache()->forget(CacheKeys::BRANDS_LIST->value);
        });

        static::updating(function ($brand) {
            $brand->setTranslations('slug', array_map(fn($item) => Str::slug($item), $brand->getTranslations('name')));

            cache()->forget(CacheKeys::BRANDS_LIST->value);
        });

        static::deleting(function () {
            cache()->forget(CacheKeys::BRANDS_LIST->value);
        });
    }
}
