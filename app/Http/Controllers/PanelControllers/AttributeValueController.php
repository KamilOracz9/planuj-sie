<?php

namespace App\Http\Controllers\PanelControllers;

use App\Enums\CacheKeys;
use App\Http\Requests\AttributeValueRequest;
use App\Http\Resources\AttributeValueResource;
use App\Models\AttributeType;
use App\Models\AttributeValue;
use Kamiloracz9\SwappableCache\Facades\SwappableCache;

class AttributeValueController extends BaseController
{
    protected string $listCacheKey = CacheKeys::ATTRIBUTE_VALUES_LIST->value;
    protected string $selectCacheKey = CacheKeys::ATTRIBUTE_VALUES_SELECT_BY_MODEL->value;
    protected string $resourceClass = AttributeValueResource::class;

    protected mixed $model;

    public function __construct()
    {
        $this->model = new AttributeValue;
    }

    public function update(AttributeValueRequest $request, int $id)
    {
        $model = AttributeValue::findOrFail($id);

        $model->update($request->validated());

        return response()->json(['id' => $model->id]);
    }

    public function create(AttributeValueRequest $request)
    {
        $model = new AttributeValue($request->validated());

        $model->save();

        return response()->json(['id' => $model->id], 201);
    }

    public function selectByModel(string $locale, string $modelType, int $modelId)
    {
        if (!$this->selectCacheKey) {
            return response()->json(['error' => 'Select cache key not defined.'], 500);
        }

        $models = SwappableCache::remember(
            key: $this->selectCacheKey . "_$locale" . "_$modelType" . "_$modelId",
            resolve: fn() => AttributeValue::queryBuilder()
                ->withAttribute()
                ->withAttributeType()
                ->filterByModel($modelType, $modelId)
                ->select(
                    AttributeValue::columnName('id'),
                    AttributeValue::columnName('data'),
                    AttributeValue::columnName('attribute_id'),
                    AttributeType::columnName('code'),
                )
                ->get()
                ->map(function ($item) {
                    $data = json_decode($item->data, true);

                    $dataValue = match ($item->code) {
                        'text', 'number', 'boolean', 'select', 'multiselect', 'date' => $data['value'] ?? null,
                        default => null,
                    };

                    return [
                        'id' => $item->id,
                        'data' => $dataValue,
                        'attribute_type_code' => $item->code,
                        'attribute_id' => $item->attribute_id,
                    ];
                })
                ->toArray(),
            fingerprint: fn() => AttributeValue::where('model_type', $modelType)->where('model_id', $modelId)->max('updated_at'),
            checkInterval: 60,
        );

        return response()->json($models);
    }
}
