<?php

namespace App\QueryBuilders;

use Illuminate\Database\Query\Builder;

class BaseQueryBuilder extends Builder
{
    protected string $modelClass = self::class;

    public function __construct()
    {
        parent::__construct(new CustomConnection);
    }

    public function __call($name, $arguments)
    {
        switch ($name) {
            case 'table':
                return $this->connection->table($this->getModel()->table ?? $this->getModel()->getTable());
        }
    }

    protected function getModel()
    {
        return new ($this->modelClass);
    }

    public function selectAndGroup(...$args)
    {
        $this->columns = [...$this->columns ?? [], ...$args];
        $this->groups = [...$this->groups ?? [], ...array_map(fn($item) => explode(' as ', strtolower($item))[0], $args)];

        return $this;
    }

    public function with(mixed $model, string $foreignKey, ?string $primaryKey = 'id', mixed $modelTrough = null)
    {
        return $this
            ->leftJoin(
                (new ($model))->getTable(),
                (new ($model))->getTable() . '.' . $primaryKey,
                ($modelTrough ? (new ($modelTrough)) : $this->getModel())->getTable() . '.' . $foreignKey,
            );
    }

    public function withTranslation(mixed $model, string $locale, string $foreignKey, ?string $primaryKey = 'id', mixed $modelTrough = null, ?string $alias = null)
    {
        $i18nTableName = (new ($model))->getTable();

        return $this
            ->leftJoin(
                $alias ? $i18nTableName . ' AS ' . $alias : $i18nTableName,
                ($alias ?? $i18nTableName) . '.' . $primaryKey,
                ($modelTrough ? (new ($modelTrough)) : $this->getModel())->getTable() . '.' . $foreignKey,
            )
            ->where(fn ($query) => $query->where(($alias ?? $i18nTableName) . '.locale', $locale)->orWhereNull(($alias ?? $i18nTableName) . '.locale'));
    }

    public function withTranslations(mixed $model, string $foreignKey, ?string $primaryKey = 'id', mixed $modelTrough = null, ?string $alias = null)
    {
        $i18nTableName = (new ($model))->getTable();

        return $this
            ->leftJoin(
                $alias ? $i18nTableName . ' AS ' . $alias : $i18nTableName,
                ($alias ?? $i18nTableName) . '.' . $primaryKey,
                ($modelTrough ? (new ($modelTrough)) : $this->getModel())->getTable() . '.' . $foreignKey,
            );
    }

    public function orderByOrderColumn()
    {
        return $this->orderBy($this->getModel()->getTable() . '.order_column');
    }

    public function customPaginate()
    {
        return $this->paginate(request()->input('limit') ?? config('panel.default_paginate_size'));
    }

    public function listExtended(string $locale)
    {
        return $this;
    }

    // Excludes rows explicitly disabled for $channelId via HasChannelVisibility
    // (channel_visibilities.is_enabled = false) - default-visible-if-no-row,
    // matching HasChannelVisibility::isEnabledForChannel()'s own fallback.
    // Deliberately own-level only (does not replicate the ancestor-group
    // cascade from HasChannelVisibility::isVisibleInChannel() - that exists
    // for storefront rendering; for this admin list filter, a Product should
    // stay visible/manageable even if its Brand happens to be hidden).
    // A safe no-op for any model with no channel_visibilities rows at all
    // (e.g. Users, Currencies), so this can be called unconditionally from
    // BaseController::index() without checking whether the model supports it.
    public function filterByChannel(?int $channelId)
    {
        if (!$channelId) {
            return $this;
        }

        $modelClass = $this->modelClass;

        return $this->whereNotIn($modelClass::columnName('id'), function ($query) use ($channelId, $modelClass) {
            $query->select('model_id')
                ->from('channel_visibilities')
                ->where('channel_id', $channelId)
                ->where('model_type', $modelClass)
                ->where('is_enabled', false);
        });
    }
}
