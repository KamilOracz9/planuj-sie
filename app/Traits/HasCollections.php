<?php

namespace App\Traits;

use App\Models\Collection;

trait HasCollections
{
    protected static function bootCollections()
    {
        static::saved(function ($model) {
            $collectionIds = request()->input('collections');

            if (!is_array($collectionIds)) {
                return;
            }

            $model->collections()->sync(array_map('intval', $collectionIds));
        });
    }

    public function collections()
    {
        return $this->belongsToMany(Collection::class, 'product_collection', 'product_id', 'collection_id');
    }
}
