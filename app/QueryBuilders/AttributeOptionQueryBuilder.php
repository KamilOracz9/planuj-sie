<?php

namespace App\QueryBuilders;

use App\Models\AttributeOption;
use App\Models\Translations\AttributeOptionTranslation;

class AttributeOptionQueryBuilder extends BaseQueryBuilder
{
    protected string $modelClass = AttributeOption::class;

    public function __construct()
    {
        parent::__construct();
    }

    public function listSelect()
    {
        return $this->select([
            AttributeOption::columnName('id'),
            AttributeOption::columnName('created_at'),
            AttributeOptionTranslation::columnName('slug'),
            AttributeOptionTranslation::columnName('name'),
        ]);
    }
}
