<?php

namespace App\Http\Requests;

use App\Models\Channel;
use App\Models\Currency;
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
            'attributes' => ['nullable', 'array'],
            'attributes.*.attribute_id' => ['required', 'integer', 'exists:attributes,id'],
            'attributes.*.data' => ['required'],
            'prices' => ['nullable', 'array', function ($attribute, $value, $fail) {
                $pairs = array_map(fn($p) => ($p['channel_id'] ?? null) . '-' . ($p['currency_id'] ?? null), $value ?? []);
                if (count($pairs) !== count(array_unique($pairs))) {
                    $fail('Duplicate channel/currency price rows are not allowed.');
                }
            }],
            'prices.*.channel_id' => ['required', 'integer', Rule::exists(Channel::tableName(), 'id')],
            'prices.*.currency_id' => ['required', 'integer', Rule::exists(Currency::tableName(), 'id')],
            'prices.*.amount' => ['required', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/'],
        ];
    }
}
