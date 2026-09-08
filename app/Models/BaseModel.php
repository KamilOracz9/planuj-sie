<?php

namespace App\Models;

use App\Traits\HasTableHelpers;
use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    use HasTableHelpers;

    public static function __callStatic($method, $parameters)
    {
        $model = new (get_called_class()) ?? new self;

        switch ($method) {
            case 'queryBuilder':
                return $model->newQueryBuilder()->table();
        }

        return parent::__callStatic($method, $parameters);
    }
}
