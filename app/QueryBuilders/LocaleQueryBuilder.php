<?php

namespace App\QueryBuilders;

use App\Models\Locale;
use App\Models\Translations\LocaleTranslation;

class LocaleQueryBuilder extends BaseQueryBuilder
{
    protected string $modelClass = Locale::class;

    public function __construct()
    {
        parent::__construct();
    }

    public function listSelect()
    {
        return $this->select([
            Locale::columnName('id'),
            Locale::columnName('code'),
            Locale::columnName('created_at'),
            LocaleTranslation::columnName('name'),
        ]);
    }
}
