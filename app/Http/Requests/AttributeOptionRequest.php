<?php

namespace App\Http\Requests;

use App\Models\AttributeOption;
use App\Models\Channel;
use App\Models\Currency;
use App\Models\Translations\AttributeOptionTranslation;
use Illuminate\Validation\Rule;

class AttributeOptionRequest extends BaseRequest
{
    protected string $modelClass = AttributeOption::class;

    public function rules(): array
    {
        $attributeOptionId = $this->route('id');

        $rules = [
            'name' => ['required', 'array'],
            'name.pl-PL' => ['required', 'string', 'max:255'],
            'name.*' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'array'],
            'slug.pl-PL' => ['required', 'string', 'max:255'],
            'order_column' => ['nullable', 'integer'],
            'attribute_id' => ['required', 'integer', 'exists:attributes,id'],
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

        // Per-locale, not `slug.*`: the DB constraint is unique(slug, locale)
        // (see 2026_08_18_183117_...), not a single global unique(slug) - the
        // same word (e.g. size "L") is a legitimate slug in both pl-PL and
        // en-US at once, since routing is locale-prefixed. A locale-blind
        // `Rule::unique('slug')` would reject that as a false duplicate.
        foreach (config('app.supported_locales') as $locale) {
            $rules["slug.$locale"] = [
                'nullable',
                'string',
                'max:255',
                Rule::unique(AttributeOptionTranslation::tableName(), 'slug')
                    ->where('locale', $locale)
                    ->ignore($attributeOptionId, AttributeOptionTranslation::FOREIGN_KEY),
            ];
        }

        return $rules;
    }
}
