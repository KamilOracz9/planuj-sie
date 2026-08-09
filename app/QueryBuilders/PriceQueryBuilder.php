<?php

namespace App\QueryBuilders;

use App\Models\Price;
use Illuminate\Support\Str;

class PriceQueryBuilder extends BaseQueryBuilder
{
    protected string $modelClass = Price::class;

    public function __construct()
    {
        parent::__construct();
    }

    public function listSelect()
    {
        return $this->select([
            Price::columnName('id'),
            Price::columnName('channel_id'),
            Price::columnName('currency_id'),
            Price::columnName('amount'),
        ]);
    }

    public function filterByModel(string $modelType, int $modelId)
    {
        return $this->where(Price::columnName('model_type'), "App\\Models\\" . ucfirst(Str::camel($modelType)))
            ->where(Price::columnName('model_id'), $modelId);
    }
}
