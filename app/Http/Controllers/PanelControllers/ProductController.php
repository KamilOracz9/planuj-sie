<?php

namespace App\Http\Controllers\PanelControllers;

use App\Enums\CacheKeys;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Attribute;
use App\Models\AttributeOption;
use App\Models\AttributeType;
use App\Models\AttributeValue;
use App\Models\Currency;
use App\Models\Price;
use App\Models\Product;
use App\Models\Translations\ProductTranslation;
use App\Models\Translations\VariantTranslation;
use App\Models\Variant;
use Illuminate\Support\Facades\DB;

class ProductController extends BaseController
{
    protected string $listCacheKey = CacheKeys::PRODUCTS_LIST->value;
    protected string $selectCacheKey = CacheKeys::PRODUCTS_SELECT->value;
    protected string $resourceClass = ProductResource::class;

    protected mixed $model;
    protected mixed $modelTranslation;

    public function __construct()
    {
        $this->model = new Product;
        $this->modelTranslation = new ProductTranslation;
    }

    public function update(ProductRequest $request, int $id)
    {
        $model = Product::findOrFail($id);

        $model->update($request->validated());

        return response()->json(['id' => $model->id]);
    }

    public function create(ProductRequest $request)
    {
        $model = new Product($request->validated());

        $model->save();

        return response()->json(['id' => $model->id], 201);
    }

    public function show(string $locale, int $id)
    {
        $response = parent::show($locale, $id);
        $data = $response->getData(true);

        if (isset($data['id'])) {
            $data['collection_ids'] = DB::table('product_collection')
                ->where('product_id', $data['id'])
                ->pluck('collection_id')
                ->all();
        }

        return response()->json($data);
    }

    // Final price = product price + variant price + priced attributes, per
    // (channel, currency, variant, attribute-option combination). "Priced
    // attributes" only come from select/multiselect attribute values (the
    // only types with an AttributeOption to hang a price on - see
    // AttributeOption's HasPrices usage). A "select" value is a single fixed
    // option (one combination); a "multiselect" value is a set of
    // *alternatives* the shopper picks one of (e.g. Rozmiar: [S, M] means
    // "available in S or M", not "bundled S+M"), so it fans out into one
    // combination per option - the price is never summed across a
    // multiselect's own options, only across different attributes chosen
    // together. Attributes cascade like everything else in this app: a
    // variant's own value for a given attribute_id overrides the product's
    // value for that same attribute (product is red, variant is green ->
    // priced as green, not red+green); attributes the variant doesn't
    // override are inherited from the product. No caching - single-product
    // admin diagnostic view, same reasoning as the visibility report and
    // by-product variants endpoints.
    public function priceBreakdown(string $locale, int $id)
    {
        $channelId = request()->integer('channel_id') ?: null;

        if (!$channelId) {
            return response()->json(['channel_id' => null, 'currencies' => []]);
        }

        $variants = Variant::queryBuilder()
            ->withTranslation(VariantTranslation::class, $locale, 'id', VariantTranslation::FOREIGN_KEY, Variant::class)
            ->filterByProduct($id)
            ->select(Variant::columnName('id'), VariantTranslation::columnName('name'))
            ->get();

        $productAttributeOptions = static::priceableAttributeOptionsByAttribute('product', $id, $locale);
        $variantAttributeOptions = $variants->mapWithKeys(
            fn($variant) => [$variant->id => static::priceableAttributeOptionsByAttribute('variant', $variant->id, $locale)]
        );

        $allOptionIds = collect(array_merge([$productAttributeOptions], $variantAttributeOptions->values()->all()))
            ->flatMap(fn($attributes) => collect($attributes)->flatMap(fn($attribute) => collect($attribute['options'])->pluck('id')))
            ->unique()
            ->values();

        $productPrices = Price::query()
            ->where('model_type', Product::class)
            ->where('model_id', $id)
            ->where('channel_id', $channelId)
            ->get()
            ->keyBy('currency_id');

        $variantPrices = Price::query()
            ->where('model_type', Variant::class)
            ->whereIn('model_id', $variants->pluck('id'))
            ->where('channel_id', $channelId)
            ->get()
            ->groupBy('currency_id');

        $optionPrices = Price::query()
            ->where('model_type', AttributeOption::class)
            ->whereIn('model_id', $allOptionIds)
            ->where('channel_id', $channelId)
            ->get()
            ->groupBy('currency_id');

        $currencies = Currency::whereIn('id', $productPrices->keys())->get()->keyBy('id');

        $result = [];

        foreach ($productPrices as $currencyId => $productPriceRow) {
            $currency = $currencies->get($currencyId);

            if (!$currency) {
                continue;
            }

            $optionAmounts = ($optionPrices->get($currencyId) ?? collect())->keyBy('model_id');

            $rows = [];
            $variantPricesForCurrency = ($variantPrices->get($currencyId) ?? collect())->keyBy('model_id');

            foreach ($variants as $variant) {
                $variantAmount = (int) ($variantPricesForCurrency->get($variant->id)->amount ?? 0);

                // Product attributes, with this variant's own values overriding by attribute_id.
                // array_replace() (not array_merge()) - attribute_id keys are integers, and
                // array_merge() renumbers/appends integer keys instead of overriding them.
                $effectiveOptions = array_replace($productAttributeOptions, $variantAttributeOptions->get($variant->id, []));

                foreach (static::combinationsOf($effectiveOptions) as $combination) {
                    $attributesAmount = (int) collect($combination)
                        ->sum(fn($choice) => $optionAmounts->get($choice['option_id'])?->amount ?? 0);

                    $rows[] = [
                        'variant_id' => $variant->id,
                        'variant_name' => $variant->name,
                        'variant_price' => $currency->toMajorUnits($variantAmount),
                        'attribute_options' => $combination,
                        'attributes_price' => $currency->toMajorUnits($attributesAmount),
                        'final_price' => $currency->toMajorUnits((int) $productPriceRow->amount + $variantAmount + $attributesAmount),
                    ];
                }
            }

            $result[] = [
                'currency_id' => $currency->id,
                'code' => $currency->code,
                'symbol' => $currency->symbol,
                'product_price' => $currency->toMajorUnits((int) $productPriceRow->amount),
                'rows' => $rows,
            ];
        }

        return response()->json(['channel_id' => $channelId, 'currencies' => $result]);
    }

    // Cartesian product across attributes: one choice per attribute, crossed
    // with every other attribute's choices. An attribute with no resolvable
    // options is dropped (doesn't blow up the whole combination set to
    // zero). No attributes at all -> a single combination with an empty
    // choice list (still one row: the variant on its own).
    private static function combinationsOf(array $effectiveOptions): array
    {
        $combinations = [[]];

        foreach ($effectiveOptions as $attribute) {
            if (empty($attribute['options'])) {
                continue;
            }

            $next = [];

            foreach ($combinations as $existing) {
                foreach ($attribute['options'] as $option) {
                    $next[] = [...$existing, [
                        'attribute_name' => $attribute['attribute_name'],
                        'option_id' => $option['id'],
                        'option_name' => $option['name'],
                    ]];
                }
            }

            $combinations = $next;
        }

        return $combinations;
    }

    // Select/multiselect attribute values only, keyed by attribute_id (rather
    // than a flat list) so a variant's own value can override the product's
    // value for the same attribute when the two are merged in
    // priceBreakdown(). Each entry carries the attribute's translated name
    // and its option ids+names, so combinationsOf() can build a human-
    // readable label for every combination.
    private static function priceableAttributeOptionsByAttribute(string $modelType, int $modelId, string $locale): array
    {
        $rows = AttributeValue::queryBuilder()
            ->withAttribute()
            ->withAttributeType()
            ->filterByModel($modelType, $modelId)
            ->select(
                AttributeValue::columnName('attribute_id'),
                AttributeValue::columnName('data'),
                AttributeType::columnName('code'),
            )
            ->get();

        $result = [];

        foreach ($rows as $row) {
            if (!in_array($row->code, ['select', 'multiselect'], true)) {
                continue;
            }

            $value = json_decode($row->data, true)['value'] ?? null;

            $optionIds = match ($row->code) {
                'select' => $value ? [(int) $value] : [],
                'multiselect' => is_array($value) ? array_map('intval', $value) : [],
            };

            if (empty($optionIds)) {
                continue;
            }

            $attribute = Attribute::find($row->attribute_id);

            $result[$row->attribute_id] = [
                'attribute_name' => $attribute?->translation($locale)->first()?->name,
                'options' => AttributeOption::whereIn('id', $optionIds)->get()
                    ->map(fn($option) => ['id' => $option->id, 'name' => $option->translation($locale)->first()?->name])
                    ->values()
                    ->all(),
            ];
        }

        return $result;
    }
}
