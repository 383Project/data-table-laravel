<?php

namespace ThreeEightThree\LaravelDataTable;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use ThreeEightThree\LaravelDataTable\Commands\DataTableMakeCommand;
use ThreeEightThree\LaravelDataTable\Commands\LaravelDataTableCommand;

class LaravelDataTableServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('data-table-laravel')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_data_table_laravel_table')
            ->hasCommand(DataTableMakeCommand::class)
            ->hasCommand(LaravelDataTableCommand::class);
    }
}
