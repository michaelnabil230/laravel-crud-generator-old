<?php

namespace MichaelNabil230\LaravelCrudGenerator;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use MichaelNabil230\LaravelCrudGenerator\Commands\LaravelCrudGeneratorCommand;

class LaravelCrudGeneratorServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-crud-generator')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel-crud-generator_table')
            ->hasCommand(LaravelCrudGeneratorCommand::class);
    }
}
