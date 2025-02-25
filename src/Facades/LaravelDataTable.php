<?php

namespace ThreeEightThree\LaravelDataTable\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \ThreeEightThree\LaravelDataTable\LaravelDataTable
 */
class LaravelDataTable extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \ThreeEightThree\LaravelDataTable\LaravelDataTable::class;
    }
}
