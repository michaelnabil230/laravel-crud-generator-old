<?php

namespace MichaelNabil230\LaravelCrudGenerator\Commands;

use Illuminate\Console\Command;

class LaravelCrudGeneratorCommand extends Command
{
    public $signature = 'laravel-crud-generator';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
