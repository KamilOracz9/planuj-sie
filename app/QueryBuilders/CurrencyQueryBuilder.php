<?php

namespace App\QueryBuilders;

use App\Models\Currency;

class CurrencyQueryBuilder extends BaseQueryBuilder
{
    protected string $modelClass = Currency::class;

    public function __construct()
    {
        parent::__construct();
    }

    public function listSelect()
    {
        return $this->select([
            Currency::columnName('id'),
            Currency::columnName('code'),
            Currency::columnName('name'),
            Currency::columnName('symbol'),
            Currency::columnName('decimal_places'),
        ]);
    }
}
