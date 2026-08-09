<?php

namespace App\QueryBuilders;

use App\Models\Series;
use App\Models\Translations\SeriesTranslation;

class SeriesQueryBuilder extends BaseQueryBuilder
{
    protected string $modelClass = Series::class;

    public function __construct()
    {
        parent::__construct();
    }

    public function listSelect()
    {
        return $this->select([
            Series::columnName('id'),
            Series::columnName('created_at'),
            SeriesTranslation::columnName('slug'),
            SeriesTranslation::columnName('name'),
        ]);
    }
}
