<?php

namespace App\Http\Requests;

use App\Models\Category;
use App\Models\Translations\CategoryTranslation;
use Illuminate\Validation\Rule;

class CategoryRequest extends BaseRequest
{
    protected string $modelClass = Category::class;

    public function rules(): array
    {
        $categoryId = $this->route('id');

        return [
            'name' => ['required', 'array'],
            'name.pl_PL' => ['required', 'string', 'max:255'],
            'name.*' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.*' => ['nullable', 'string', 'max:500'],
            'short_description' => ['nullable', 'array'],
            'short_description.*' => ['nullable', 'string', 'max:500'],
            'slug' => ['required', 'array'],
            'slug.pl_PL' => ['required', 'string', 'max:255'],
            'slug.*' => ['nullable', 'string', 'max:255', Rule::unique(CategoryTranslation::tableName(), 'slug')->ignore($categoryId, CategoryTranslation::FOREIGN_KEY)],
        ];
    }
}
