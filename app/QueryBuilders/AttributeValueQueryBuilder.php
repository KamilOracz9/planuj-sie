<?php

namespace App\QueryBuilders;

use App\Models\Attribute;
use App\Models\AttributeType;
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
            AttributeValue::columnName('attribute_id'),
            AttributeValue::columnName('data'),
            AttributeValue::columnName('model_id'),
            AttributeValue::columnName('model_type'),
            AttributeValue::columnName('created_at'),
        ]);
    }

    public function filterByModel(string $modelType, int $modelId)
    {
        return $this->where(AttributeValue::columnName('model_type'), "App\\Models\\" . ucfirst(\Illuminate\Support\Str::camel($modelType)))
            ->where(AttributeValue::columnName('model_id'), $modelId);
    }

    public function withAttribute()
    {
        return $this->leftJoin(
            Attribute::tableName(),
            Attribute::tableName() . '.id',
            '=',
            AttributeValue::tableName() . '.attribute_id'
        );
    }

    public function withAttributeType()
    {
        return $this->leftJoin(
            AttributeType::tableName(),
            AttributeType::tableName() . '.id',
            '=',
            Attribute::tableName() . '.attribute_type_id'
        );
    }
}
