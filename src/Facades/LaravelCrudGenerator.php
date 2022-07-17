<?php

namespace MichaelNabil230\LaravelCrudGenerator\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \MichaelNabil230\LaravelCrudGenerator\LaravelCrudGenerator
 */
class LaravelCrudGenerator extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-crud-generator';
    }
}
