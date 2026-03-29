<?php

namespace App\QueryBuilders;

use App\Models\Product;
use App\Models\Translations\ProductTranslation;

class ProductQueryBuilder extends BaseQueryBuilder
{
    protected string $modelClass = Product::class;

    public function __construct()
    {
        parent::__construct();
    }

    public function listSelect()
    {
        return $this->select([
            Product::columnName('id'),
            ProductTranslation::columnName('slug'),
            ProductTranslation::columnName('name'),
        ]);
    }
}
