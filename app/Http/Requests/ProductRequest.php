<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Models\Translations\ProductTranslation;
use Illuminate\Validation\Rule;

class ProductRequest extends BaseRequest
{
    protected string $modelClass = Product::class;

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
            'slug.*' => ['nullable', 'string', 'max:255', Rule::unique(ProductTranslation::tableName(), 'slug')->ignore($productId, ProductTranslation::FOREIGN_KEY)],
        ];
    }
}
