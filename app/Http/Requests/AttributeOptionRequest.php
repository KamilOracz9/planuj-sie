<?php

namespace App\Http\Requests;

use App\Models\AttributeOption;
use App\Models\Translations\AttributeOptionTranslation;
use Illuminate\Validation\Rule;

class AttributeOptionRequest extends BaseRequest
{
    protected string $modelClass = AttributeOption::class;

    public function rules(): array
    {
        $attributeOptionId = $this->route('id');

        return [
            'name' => ['required', 'array'],
            'name.pl-PL' => ['required', 'string', 'max:255'],
            'name.*' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'array'],
            'slug.pl-PL' => ['required', 'string', 'max:255'],
            'slug.*' => ['nullable', 'string', 'max:255', Rule::unique(AttributeOptionTranslation::tableName(), 'slug')->ignore($attributeOptionId, AttributeOptionTranslation::FOREIGN_KEY)],
            'order_column' => ['nullable', 'integer'],
            'attribute_id' => ['required', 'integer', 'exists:attributes,id'],
        ];
    }
}
