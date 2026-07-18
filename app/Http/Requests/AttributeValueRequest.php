<?php

namespace App\Http\Requests;

use App\Models\AttributeValue;

class AttributeValueRequest extends BaseRequest
{
    protected string $modelClass = AttributeValue::class;

    public function rules(): array
    {
        return [
            'value' => ['required', 'string', 'max:255'],
            'order_column' => ['nullable', 'integer'],
        ];
    }
}
