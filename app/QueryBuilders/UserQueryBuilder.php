<?php

namespace App\QueryBuilders;

use App\Models\User;

class UserQueryBuilder extends BaseQueryBuilder
{
    protected string $modelClass = User::class;

    public function __construct()
    {
        parent::__construct();
    }

    public function listSelect()
    {
        return $this->select([
            User::columnName('id'),
            User::columnName('email'),
            User::columnName('name'),
            User::columnName('created_at'),
        ]);
    }
}
