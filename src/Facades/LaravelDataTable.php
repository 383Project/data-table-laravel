<?php

namespace Team383\LaravelDataTable\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Team383\LaravelDataTable\LaravelDataTable
 */
class LaravelDataTable extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Team383\LaravelDataTable\LaravelDataTable::class;
    }
}
