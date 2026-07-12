<?php

namespace App\Http\Requests;

use App\Models\Attribute;
use App\Models\Translations\AttributeTranslation;
use Illuminate\Validation\Rule;

class AttributeRequest extends BaseRequest
{
    protected string $modelClass = Attribute::class;

    public function rules(): array
    {
        $productId = $this->route('id');

        return [
            'name' => ['required', 'array'],
            'name.pl-PL' => ['required', 'string', 'max:255'],
            'name.*' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'array'],
            'slug.pl-PL' => ['required', 'string', 'max:255'],
            'slug.*' => ['nullable', 'string', 'max:255', Rule::unique(AttributeTranslation::tableName(), 'slug')->ignore($productId, AttributeTranslation::FOREIGN_KEY)],
            'order_column' => ['nullable', 'integer'],
        ];
    }
}
