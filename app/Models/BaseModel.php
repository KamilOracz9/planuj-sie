<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

abstract class BaseModel extends Model
{
    public static function __callStatic($method, $parameters)
    {
        $model = new (get_called_class()) ?? new self;

        switch ($method) {
            case 'queryBuilder':
                return $model->newQueryBuilder()->table();
        }

        return parent::__callStatic($method, $parameters);
    }

    public static function tableName(): string
    {
        return (new static)->getTable();
    }

    public static function columnName(string $column): string
    {
        return self::tableName() . '.' . $column;
    }

    public static function modelName(): string
    {
        return Str::snake(Arr::last(explode('\\', get_called_class())));
    }
}
