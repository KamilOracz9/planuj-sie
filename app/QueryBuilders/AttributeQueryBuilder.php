<?php

namespace App\QueryBuilders;

use App\Models\Attribute;
use App\Models\Translations\AttributeTranslation;

class AttributeQueryBuilder extends BaseQueryBuilder
{
    protected string $modelClass = Attribute::class;

    public function __construct()
    {
        parent::__construct();
    }

    public function listSelect()
    {
        return $this->select([
            Attribute::columnName('id'),
            AttributeTranslation::columnName('slug'),
            AttributeTranslation::columnName('name'),
            AttributeTranslation::columnName('value'),
        ]);
    }
}
