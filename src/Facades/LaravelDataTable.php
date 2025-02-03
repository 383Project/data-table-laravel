<?php

namespace SteJaySulli\LaravelDataTable\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \SteJaySulli\LaravelDataTable\LaravelDataTable
 */
class LaravelDataTable extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \SteJaySulli\LaravelDataTable\LaravelDataTable::class;
    }
}
