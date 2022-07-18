<?php

namespace MichaelNabil230\LaravelCrudGenerator;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelCrudGeneratorServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-crud-generator')
            ->hasConfigFile()
            ->hasViews()
            ->hasCommands([
                Commands\CrudCommand::class,
                Commands\CrudRequestCommand::class,
                Commands\CrudControllerCommand::class,
                Commands\CrudModelCommand::class,
                Commands\CrudMigrationCommand::class,
                Commands\CrudViewCommand::class,
                Commands\CrudLangCommand::class,
            ]);
    }
}
