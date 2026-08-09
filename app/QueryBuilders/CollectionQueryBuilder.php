<?php

namespace App\QueryBuilders;

use App\Models\Collection;
use App\Models\Translations\CollectionTranslation;

class CollectionQueryBuilder extends BaseQueryBuilder
{
    protected string $modelClass = Collection::class;

    public function __construct()
    {
        parent::__construct();
    }

    public function listSelect()
    {
        return $this->select([
            Collection::columnName('id'),
            Collection::columnName('created_at'),
            CollectionTranslation::columnName('slug'),
            CollectionTranslation::columnName('name'),
        ]);
    }
}
