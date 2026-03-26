<?php

namespace App\QueryBuilders;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Grammars\MySqlGrammar;
use Illuminate\Database\Query\Processors\MySqlProcessor;
use Illuminate\Support\Facades\DB;

class CustomConnection extends Connection
{
    public function __construct()
    {
        parent::__construct(
            $this->pdo = DB::connection()->pdo,
            $this->database = DB::connection()->database,
            $this->tablePrefix = DB::connection()->tablePrefix,
            $this->config = DB::connection()->config,
        );
    }

    protected function getDefaultQueryGrammar()
    {
        return (new MySqlGrammar($this));
    }

    protected function getDefaultPostProcessor()
    {
        return new MySqlProcessor;
    }

    public function query()
    {
        return new (debug_backtrace()[2]['object']::class)(
            $this,
            $this->getQueryGrammar(),
            $this->getPostProcessor()
        );
    }
}