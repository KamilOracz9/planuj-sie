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
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Data-only migration (no schema change): creates the MediaCollection rows
// that replace the old hardcoded Has*Media traits 1:1, plus the user's
// example collections, then auto-attaches the replacement collections to
// every existing model instance so nothing loses its media after the
// rework (the old system had no explicit "attach" step - every model of a
// given class always had every one of its class's collections available).
return new class extends Migration
{
    private const REPLACEMENT_COLLECTIONS = [
        ['code' => 'logo', 'name' => 'Logo', 'kind' => 'image', 'type' => 'single'],
        ['code' => 'icon', 'name' => 'Icon', 'kind' => 'image', 'type' => 'single'],
        ['code' => 'gallery', 'name' => 'Gallery', 'kind' => 'image', 'type' => 'multiple'],
        ['code' => 'main_image', 'name' => 'Main image', 'kind' => 'image', 'type' => 'single'],
        ['code' => 'main_image_2', 'name' => 'Main image 2', 'kind' => 'image', 'type' => 'single'],
        ['code' => 'documents', 'name' => 'Documents', 'kind' => 'document', 'type' => 'multiple'],
    ];

    private const EXAMPLE_COLLECTIONS = [
        ['code' => 'packshot', 'name' => 'Packshot', 'kind' => 'image', 'type' => 'multiple'],
        ['code' => 'tech', 'name' => 'Tech', 'kind' => 'image', 'type' => 'multiple'],
        ['code' => 'listing_image', 'name' => 'Listing image', 'kind' => 'image', 'type' => 'single'],
        ['code' => 'manual', 'name' => 'Manual', 'kind' => 'document', 'type' => 'multiple'],
        ['code' => 'karta_techniczna', 'name' => 'Karta techniczna', 'kind' => 'document', 'type' => 'single'],
    ];

    // Which replacement collection codes each model class used to hardcode.
    private const AUTO_ATTACH = [
        Brand::class => ['logo', 'documents'],
        Series::class => ['logo', 'documents'],
        Collection::class => ['logo', 'documents'],
        Category::class => ['icon', 'documents'],
        Attribute::class => ['icon'],
        AttributeOption::class => ['icon'],
        AttributeValue::class => ['icon'],
        Product::class => ['gallery', 'main_image', 'main_image_2', 'documents'],
        Variant::class => ['gallery', 'main_image', 'main_image_2', 'documents'],
    ];

    public function up(): void
    {
        $now = now();

        foreach ([...self::REPLACEMENT_COLLECTIONS, ...self::EXAMPLE_COLLECTIONS] as $collection) {
            DB::table('media_collections')->insertOrIgnore([
                ...$collection,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $collectionIds = DB::table('media_collections')->pluck('id', 'code');

        foreach (self::AUTO_ATTACH as $modelClass => $codes) {
            $modelIds = $modelClass::query()->pluck('id');

            foreach ($codes as $code) {
                $mediaCollectionId = $collectionIds[$code] ?? null;

                if (!$mediaCollectionId) {
                    continue;
                }

                $rows = $modelIds->map(fn($id) => [
                    'media_collection_id' => $mediaCollectionId,
                    'model_type' => $modelClass,
                    'model_id' => $id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all();

                if ($rows) {
                    DB::table('model_media_collections')->insertOrIgnore($rows);
                }
            }
        }
    }

    public function down(): void
    {
        $codes = array_column([...self::REPLACEMENT_COLLECTIONS, ...self::EXAMPLE_COLLECTIONS], 'code');

        $collectionIds = DB::table('media_collections')->whereIn('code', $codes)->pluck('id');

        DB::table('model_media_collections')->whereIn('media_collection_id', $collectionIds)->delete();
        DB::table('media_collections')->whereIn('code', $codes)->delete();
    }
};
