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
            ->where(($alias ?? $i18nTableName) . '.locale', $locale);
    }

    // public function isActive()
    // {
    //     return $this->where($this->getModel()->getTable() . '.enabled', true);
    // }

    // public function isEnabled()
    // {
    //     return $this->where($this->getModel()->getTable() . '.enabled', true);
    // }

    public function orderByOrderColumn()
    {
        return $this->orderBy($this->getModel()->getTable() . '.order_column');
    }

    public function customPaginate()
    {
        return $this->paginate(request()->get('limit') ?? config('panel.default_paginate_size'));
    }
}
