<?php

namespace App\Http\Requests;

use App\Models\AttributeOption;

class AttributeOptionRequest extends BaseRequest
{
    protected string $modelClass = AttributeOption::class;

    public function rules(): array
    {
        return [
            'name' => ['required', 'array'],
            'name.pl-PL' => ['required', 'string', 'max:255'],
            'name.*' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'array'],
            'slug.pl-PL' => ['required', 'string', 'max:255'],
            'slug.*' => ['nullable', 'string', 'max:255'],
            'order_column' => ['nullable', 'integer'],
            'attribute_id' => ['required', 'integer', 'exists:attributes,id'],
        ];
    }
}
