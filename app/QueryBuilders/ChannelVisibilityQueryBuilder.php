<?php

namespace App\QueryBuilders;

use App\Models\ChannelVisibility;
use Illuminate\Support\Str;

class ChannelVisibilityQueryBuilder extends BaseQueryBuilder
{
    protected string $modelClass = ChannelVisibility::class;

    public function __construct()
    {
        parent::__construct();
    }

    public function listSelect()
    {
        return $this->select([
            ChannelVisibility::columnName('id'),
            ChannelVisibility::columnName('channel_id'),
            ChannelVisibility::columnName('is_enabled'),
        ]);
    }

    public function filterByModel(string $modelType, int $modelId)
    {
        return $this->where(ChannelVisibility::columnName('model_type'), "App\\Models\\" . ucfirst(Str::camel($modelType)))
            ->where(ChannelVisibility::columnName('model_id'), $modelId);
    }
}
