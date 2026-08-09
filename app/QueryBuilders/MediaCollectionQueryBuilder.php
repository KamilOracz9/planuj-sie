<?php

namespace App\QueryBuilders;

use App\Models\MediaCollection;

class MediaCollectionQueryBuilder extends BaseQueryBuilder
{
    protected string $modelClass = MediaCollection::class;

    public function __construct()
    {
        parent::__construct();
    }

    public function listSelect()
    {
        return $this->select([
            MediaCollection::columnName('id'),
            MediaCollection::columnName('code'),
            MediaCollection::columnName('name'),
            MediaCollection::columnName('kind'),
            MediaCollection::columnName('type'),
        ]);
    }
}
