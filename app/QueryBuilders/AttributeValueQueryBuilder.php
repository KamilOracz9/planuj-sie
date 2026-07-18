<?php

namespace App\QueryBuilders;

use App\Models\AttributeValue;

class AttributeValueQueryBuilder extends BaseQueryBuilder
{
    protected string $modelClass = AttributeValue::class;

    public function __construct()
    {
        parent::__construct();
    }

    public function listSelect()
    {
        return $this->select([
            AttributeValue::columnName('id'),
            AttributeValue::columnName('value'),
            AttributeValue::columnName('created_at'),
        ]);
    }
}
