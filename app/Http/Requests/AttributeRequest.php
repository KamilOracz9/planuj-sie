<?php

namespace App\Http\Requests;

use App\Models\Attribute;
use App\Models\Translations\VariantTranslation;
use Illuminate\Validation\Rule;

class AttributeRequest extends BaseRequest
{
    protected string $modelClass = Attribute::class;

    public function rules(): array
    {
        $productId = $this->route('id');

        return [
            'name' => ['required', 'array'],
            'name.pl_PL' => ['required', 'string', 'max:255'],
            'name.*' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'array'],
            'slug.pl_PL' => ['required', 'string', 'max:255'],
            'slug.*' => ['nullable', 'string', 'max:255', Rule::unique(VariantTranslation::tableName(), 'slug')->ignore($productId, VariantTranslation::FOREIGN_KEY)],
            'model_id' => ['required', 'integer'],
            'model_type' => ['required', 'string'],
            'order_column' => ['nullable', 'integer'],
            'value' => ['required', 'array'],
            'value.pl_PL' => ['required', 'string', 'max:255'],
            'value.*' => ['nullable', 'string', 'max:255'],
        ];
    }
}
