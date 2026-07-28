<?php

namespace App\Traits;

use App\Http\Repositories\AttributeRepository;
use App\Enums\CacheKeys;
use App\Models\AttributeValue;
use Illuminate\Support\Facades\DB;

trait HasAttributes
{
    protected static function bootAttributes()
    {
        static::saved(function ($model) {
            $attributes = request()->input('attributes');

            if (!is_array($attributes)) {
                return;
            }

            DB::transaction(function () use ($model, $attributes) {
                $model->deleteAttributeValues();

                foreach ($attributes as $attribute) {
                    (new AttributeValue([
                        'model_id'     => $model->id,
                        'model_type'   => get_class($model),
                        'attribute_id' => $attribute['attribute_id'],
                        'data'         => json_encode(match (AttributeRepository::getAttributeType($attribute['attribute_id'])) {
                            'text'        => ['value' => $attribute['data']],
                            'number'      => ['value' => is_numeric($attribute['data']) ? $attribute['data'] + 0 : null],
                            'boolean'     => ['value' => filter_var($attribute['data'], FILTER_VALIDATE_BOOLEAN)],
                            'select'      => ['value' => (int) $attribute['data']],
                            'multiselect' => ['value' => array_map('intval', (array) $attribute['data'])],
                            'date'        => ['value' => $attribute['data']],
                            default => null,
                        }),
                    ]))->save();
                }
            });

            static::clearAttributeValuesSelectCache($model);
        });

        static::deleted(function ($model) {
            $model->deleteAttributeValues();

            static::clearAttributeValuesSelectCache($model);
        });
    }

    private static function clearAttributeValuesSelectCache($model)
    {
        foreach (config('app.supported_locales') as $locale) {
            cache()->forget(CacheKeys::ATTRIBUTE_VALUES_SELECT_BY_MODEL->value . "_$locale" . "_" . $model::modelName() . "_$model->id");
        }
    }

    public function attributeValues()
    {
        return AttributeValue::query()
            ->where('model_type', get_class($this))
            ->where('model_id', $this->id)
            ->get();
    }

    private function deleteAttributeValues()
    {
        DB::transaction(function () {
            AttributeValue::query()
                ->where('model_type', get_class($this))
                ->where('model_id', $this->id)
                ->delete();
        });
    }
}
