<?php

namespace SteJaySulli\LaravelDataTable\Commands;

use Illuminate\Console\Command;

class LaravelDataTableCommand extends Command
{
    public $signature = 'data-table-laravel';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
