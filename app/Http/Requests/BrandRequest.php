<?php

namespace App\Http\Requests;

use App\Models\Brand;
use Illuminate\Validation\Rule;

class BrandRequest extends BaseRequest
{
    protected string $modelClass = Brand::class;

    public function rules(): array
    {
        $brandId = $this->route('id');

        return [
            'name' => ['required', 'array'],
            'name.pl-PL' => ['required', 'string', 'max:255'],
            'name.*' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'array'],
            'slug.pl-PL' => ['required', 'string', 'max:255'],
            'slug.*' => ['nullable', 'string', 'max:255', Rule::unique('brand_translations', 'slug')->ignore($brandId, 'brand_id')],
        ];
    }
}
