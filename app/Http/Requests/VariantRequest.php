<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Models\Translations\VariantTranslation;
use App\Models\Variant;
use Illuminate\Validation\Rule;

class VariantRequest extends BaseRequest
{
    protected string $modelClass = Variant::class;

    public function rules(): array
    {
        $productId = $this->route('id');

        return [
            'name' => ['required', 'array'],
            'name.pl-PL' => ['required', 'string', 'max:255'],
            'name.*' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.*' => ['nullable', 'string', 'max:500'],
            'short_description' => ['nullable', 'array'],
            'short_description.*' => ['nullable', 'string', 'max:500'],
            'slug' => ['required', 'array'],
            'slug.pl-PL' => ['required', 'string', 'max:255'],
            'slug.*' => ['nullable', 'string', 'max:255', Rule::unique(VariantTranslation::tableName(), 'slug')->ignore($productId, VariantTranslation::FOREIGN_KEY)],
            'product_id' => ['required', 'integer', Rule::exists(Product::tableName(), 'id')],
        ];
    }
}
