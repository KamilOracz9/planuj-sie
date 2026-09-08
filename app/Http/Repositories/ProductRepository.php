<?php

namespace App\Http\Repositories;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Kamiloracz9\SwappableCache\Facades\SwappableCache;

class ProductRepository
{
    /**
     * The ids of every Collection this Product belongs to (see
     * App\Traits\HasCollections / the product_collection pivot).
     */
    public static function collectionIds(int $productId): array
    {
        return SwappableCache::remember(
            key: "product_collection_ids_{$productId}",
            resolve: fn () => DB::table('product_collection')
                ->where('product_id', $productId)
                ->pluck('collection_id')
                ->all(),
            // Collections are synced from the same save() cycle as the
            // product's own attributes (HasCollections' `saved` hook), so
            // the product's own updated_at already captures this change.
            fingerprint: fn () => Product::where('id', $productId)->value('updated_at'),
            checkInterval: 60,
        );
    }
}
