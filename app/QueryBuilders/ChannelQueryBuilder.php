<?php

namespace App\QueryBuilders;

use App\Models\Channel;
use App\Models\Translations\ChannelTranslation;

class ChannelQueryBuilder extends BaseQueryBuilder
{
    protected string $modelClass = Channel::class;

    public function __construct()
    {
        parent::__construct();
    }

    public function listSelect()
    {
        return $this->select([
            Channel::columnName('id'),
            ChannelTranslation::columnName('slug'),
            ChannelTranslation::columnName('name'),
        ]);
    }
}
