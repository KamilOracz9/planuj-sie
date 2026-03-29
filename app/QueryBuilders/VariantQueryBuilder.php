<?php

namespace App\QueryBuilders;

use App\Models\Translations\VariantTranslation;
use App\Models\Variant;

class VariantQueryBuilder extends BaseQueryBuilder
{
    protected string $modelClass = Variant::class;

    public function __construct()
    {
        parent::__construct();
    }

    public function listSelect()
    {
        return $this->select([
            Variant::columnName('id'),
            VariantTranslation::columnName('slug'),
            VariantTranslation::columnName('name'),
        ]);
    }
}
