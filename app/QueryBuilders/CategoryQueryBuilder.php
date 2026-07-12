<?php

namespace App\QueryBuilders;

use App\Models\Category;
use App\Models\Translations\CategoryTranslation;

class CategoryQueryBuilder extends BaseQueryBuilder
{
    protected string $modelClass = Category::class;

    public function __construct()
    {
        parent::__construct();
    }

    public function listExtended(string $locale)
    {
        return $this
            ->withTranslation(CategoryTranslation::class, $locale, 'parent_id', CategoryTranslation::FOREIGN_KEY, Category::class, Category::PARENT_CATEGORY_TRANSLATIONTABLE_ALIAS);
    }

    public function listSelect()
    {
        return $this->select([
            Category::columnName('id'),
            Category::columnName('parent_id'),
            Category::columnName('created_at'),
            CategoryTranslation::columnName('slug'),
            CategoryTranslation::columnName('name'),
            Category::PARENT_CATEGORY_TRANSLATIONTABLE_ALIAS . '.name' . ' AS parent_name',
            Category::PARENT_CATEGORY_TRANSLATIONTABLE_ALIAS . '.slug' . ' AS parent_slug',
        ]);
    }
}
