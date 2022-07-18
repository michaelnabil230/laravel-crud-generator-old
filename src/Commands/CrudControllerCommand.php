<?php

namespace MichaelNabil230\LaravelCrudGenerator\Commands;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Illuminate\Console\GeneratorCommand;

class CrudControllerCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:controller
                            {name : The name of the controller.}
                            {--model= : The name of the Model.}
                            {--controller-namespace= : Namespace of the controller.}
                            {--type=web : The type of crud web or api.}
                            {--view-path= : The name of the view path.}
                            {--fields= : Field names for the form & migration.}
                            {--validations= : Validation rules for the fields.}
                            {--route-group= : Prefix of the route group.}
                            {--force : Overwrite already existing controller.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new resource controller.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Controller';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        if (!config('crud-generator.custom_template')) {
            if ($this->option('type') == 'web') {
                return __DIR__ . '/../stubs/controller.stub';
            } else {
                return __DIR__ . '/../stubs/controller-api.stub';
            }
        } else {
            if ($this->option('type') == 'web') {
                return config('crud-generator.path') . '/controller.stub';
            } else {
                return config('crud-generator.path') . '/controller-api.stub';
            }
        }
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return trim($this->argument('name')) . 'Controller';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\' . ($this->option('controller-namespace') ? $this->option('controller-namespace') : 'Http\Controllers');
    }

    /**
     * Determine if the class already exists.
     *
     * @param  string  $rawName
     * @return bool
     */
    protected function alreadyExists($rawName)
    {
        if ($this->option('force')) {
            return false;
        }

        return parent::alreadyExists($rawName);
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $viewPath = $this->option('view-path') ? $this->option('view-path') . '.' : '';
        $crudName = strtolower($this->argument('name'));
        $crudNameSingular = Str::singular($crudName);
        $crudNamePlural = Str::plural($crudName);
        $modelClass = $this->parseModel($this->option('model'));
        $routeGroup = ($this->option('route-group')) ? $this->option('route-group') . '/' : '';
        $routePrefix = ($this->option('route-group')) ? $this->option('route-group') : '';
        $routePrefixCap = ucfirst($routePrefix);
        $viewName = Str::snake($this->argument('name'), '-');
        $fields = $this->option('fields');
        $validations = rtrim($this->option('validations'), ';');

        $replace = [
            '{{viewName}}' => $viewName,
            '{{viewPath}}' => $viewPath,
            '{{modelVariableSingular}}' => $crudNameSingular,
            '{{modelVariablePlural}}' => $crudNamePlural,
            '{{routePrefix}}' => $routePrefix,
            '{{routePrefixCap}}' => $routePrefixCap,
            '{{routeGroup}}' => $routeGroup,
        ];

        $replace = $this->buildModelReplacements($replace, $modelClass);
        $replace = $this->buildFormRequestReplacements($replace, $modelClass, $validations);
        $replace = $this->buildFileSnippetReplacements($replace, $fields);

        return str_replace(
            array_keys($replace),
            array_values($replace),
            parent::buildClass($name)
        );
    }

    /**
     * Build the model replacement values.
     *
     * @param  array  $replace
     * @return array
     */
    protected function buildModelReplacements(array $replace, $modelClass)
    {
        return array_merge($replace, [
            'DummyFullModelClass' => $modelClass,
            '{{ namespacedModel }}' => $modelClass,
            '{{namespacedModel}}' => $modelClass,
            'DummyModelClass' => class_basename($modelClass),
            '{{ model }}' => class_basename($modelClass),
            '{{model}}' => class_basename($modelClass),
            'DummyModelVariable' => lcfirst(class_basename($modelClass)),
            '{{ modelVariable }}' => lcfirst(class_basename($modelClass)),
            '{{modelVariable}}' => lcfirst(class_basename($modelClass)),
        ]);
    }

    /**
     * Get the fully-qualified model class name.
     *
     * @param  string  $model
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function parseModel($model)
    {
        if (preg_match('([^A-Za-z0-9_/\\\\])', $model)) {
            throw new InvalidArgumentException('Model name contains invalid characters.');
        }

        return $this->qualifyModel($model);
    }

    protected function buildFileSnippetReplacements($replace, $fields)
    {
        $snippet = <<<EOD
        if (\$request->hasFile('{{fieldName}}')) {
            \$requestData['{{fieldName}}'] = \$request->file('{{fieldName}}')->store('uploads', 'public');
        }
        EOD;

        $fieldsArray = explode(';', $fields);
        $fileSnippet = '';

        if ($fields) {
            foreach ($fieldsArray as $index => $item) {
                $itemArray = explode('#', $item);
                if (trim($itemArray[1]) == 'file') {
                    $fileSnippet .= str_replace('{{fieldName}}', trim($itemArray[0]), $snippet) . "\n";
                }
            }
        }

        return array_merge($replace, [
            '{{fileSnippet}}' => $fileSnippet,
        ]);
    }

    /**
     * Build the model replacement values.
     *
     * @param  array  $replace
     * @param  string  $modelClass
     * @return array
     */
    protected function buildFormRequestReplacements(array $replace, $modelClass, $validations)
    {
        [$namespace, $storeRequestClass, $updateRequestClass] = [
            'Illuminate\\Http', 'Request', 'Request',
        ];

        $namespace = 'App\\Http\\Requests';

        [$storeRequestClass, $updateRequestClass] = $this->generateFormRequests(
            $modelClass,
            $storeRequestClass,
            $updateRequestClass,
            $validations,
        );

        $namespacedRequests = $namespace . '\\' . $storeRequestClass . ';';

        if ($storeRequestClass !== $updateRequestClass) {
            $namespacedRequests .= PHP_EOL . 'use ' . $namespace . '\\' . $updateRequestClass . ';';
        }

        return array_merge($replace, [
            '{{ storeRequest }}' => $storeRequestClass,
            '{{storeRequest}}' => $storeRequestClass,
            '{{ updateRequest }}' => $updateRequestClass,
            '{{updateRequest}}' => $updateRequestClass,
            '{{ namespacedStoreRequest }}' => $namespace . '\\' . $storeRequestClass,
            '{{namespacedStoreRequest}}' => $namespace . '\\' . $storeRequestClass,
            '{{ namespacedUpdateRequest }}' => $namespace . '\\' . $updateRequestClass,
            '{{namespacedUpdateRequest}}' => $namespace . '\\' . $updateRequestClass,
            '{{ namespacedRequests }}' => $namespacedRequests,
            '{{namespacedRequests}}' => $namespacedRequests,
        ]);
    }

    /**
     * Generate the form requests for the given model and classes.
     *
     * @param  string  $modelClass
     * @param  string  $storeRequestClass
     * @param  string  $updateRequestClass
     * @param  array  $validations
     * @return array
     */
    protected function generateFormRequests($modelClass, $storeRequestClass, $updateRequestClass, $validations)
    {
        $storeRequestClass = 'Store' . class_basename($modelClass) . 'Request';

        $this->call('crud:request', [
            'name' => $storeRequestClass,
            '--validations' => $validations,
        ]);

        $updateRequestClass = 'Update' . class_basename($modelClass) . 'Request';

        $this->call('crud:request', [
            'name' => $updateRequestClass,
            '--validations' => $validations,
        ]);

        return [$storeRequestClass, $updateRequestClass];
    }
}
