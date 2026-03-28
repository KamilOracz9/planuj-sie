<?php

namespace App\QueryBuilders;

use App\Models\Brand;
use App\Models\Translations\BrandTranslation;

class BrandQueryBuilder extends BaseQueryBuilder
{
    protected string $modelClass = Brand::class;

    public function __construct()
    {
        parent::__construct();
    }

    public function listSelect()
    {
        return $this->select([
            Brand::columnName('id'),
            BrandTranslation::columnName('slug'),
            BrandTranslation::columnName('name'),
        ]);
    }
}
