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

    public function listSelect()
    {
        return $this->select([
            Category::columnName('id'),
            CategoryTranslation::columnName('slug'),
            CategoryTranslation::columnName('name'),
        ]);
    }
}
