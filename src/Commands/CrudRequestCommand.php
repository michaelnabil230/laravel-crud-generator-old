<?php

namespace MichaelNabil230\LaravelCrudGenerator\Commands;

use Illuminate\Console\GeneratorCommand;

class CrudRequestCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:request
                            {name : The name of the Request.}
                            {--validations= : Validation rules for the fields.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new request.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Request';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return config('crud-generator.custom_template')
            ? config('crud-generator.path').'/request.stub'
            : __DIR__.'/../stubs/request.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Http\Requests';
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $replace = [];

        $replace = $this->buildRulesReplacements($replace);

        return str_replace(
            array_keys($replace),
            array_values($replace),
            parent::buildClass($name)
        );
    }

    /**
     * Build the replacements for the validation rules.
     *
     * @param  array  $replace
     * @return array
     */
    protected function buildRulesReplacements($replace)
    {
        $validations = $this->option('validations');

        $validationRules = '';
        if (trim($validations) != '') {
            $rules = explode(';', $validations);

            collect($rules)
                ->filter()
                ->map(function ($rule) use (&$validationRules) {
                    // extract field name and args
                    $parts = explode('#', $rule);
                    $fieldName = trim($parts[0]);
                    $rules = trim($parts[1]);
                    $validationRules .= "\t\t\t\t\t\t\t\t\t\t\t'$fieldName' => ".json_encode(explode('|', $rules)).",\n";
                });

            $validationRules = substr($validationRules, 0, -1); // lose the last comma
        }

        return array_merge($replace, [
            '{{rules}}' => $validationRules,
        ]);
    }
}
