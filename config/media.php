<?php

use App\Models\Attribute;
use App\Models\AttributeOption;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\Series;
use App\Models\Variant;

return [

    /*
     * Explicit allow-list of {modelType} route segment => model class for
     * the generic media/media-collections routes registered in
     * MediaCollection::routes(). Kept in sync with panel's MediaModelType
     * union (panel/features/media/types.ts) - these are the exact plural
     * kebab-case values already used by the frontend's media routes today,
     * not a derived/guessed transformation (PriceQueryBuilder::filterByModel's
     * Str::camel trick doesn't round-trip correctly for multi-word types
     * like "attribute-options", and this controller performs writes, not
     * just reads, so an explicit list is used instead of a string transform).
     */
    'model_types' => [
        'products' => Product::class,
        'variants' => Variant::class,
        'brands' => Brand::class,
        'series' => Series::class,
        'collections' => Collection::class,
        'categories' => Category::class,
        'attributes' => Attribute::class,
        'attribute-options' => AttributeOption::class,
        'attribute-values' => AttributeValue::class,
    ],

];
