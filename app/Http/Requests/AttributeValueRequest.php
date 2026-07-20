<?php

namespace App\Http\Requests;

use App\Models\AttributeValue;

class AttributeValueRequest extends BaseRequest
{
    protected string $modelClass = AttributeValue::class;

    public function rules(): array
    {
        return [
            'data' => ['required', 'json'],
            'attribute_id' => ['required', 'integer', 'exists:attributes,id'],
            'order_column' => ['nullable', 'integer'],
            'model_id' => ['required', 'string'],
            'model_type' => ['required', 'string'],
        ];
    }
}
