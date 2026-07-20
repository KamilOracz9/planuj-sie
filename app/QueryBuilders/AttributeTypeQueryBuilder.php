<?php

namespace App\QueryBuilders;

use App\Models\AttributeType;
use App\Models\Translations\AttributeTypeTranslation;

class AttributeTypeQueryBuilder extends BaseQueryBuilder
{
    protected string $modelClass = AttributeType::class;

    public function __construct()
    {
        parent::__construct();
    }

    public function listSelect()
    {
        return $this->select([
            AttributeType::columnName('id'),
            AttributeType::columnName('created_at'),
            AttributeTypeTranslation::columnName('slug'),
            AttributeTypeTranslation::columnName('name'),
        ]);
    }
}
