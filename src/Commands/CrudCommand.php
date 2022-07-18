<?php

namespace MichaelNabil230\LaravelCrudGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use MichaelNabil230\LaravelCrudGenerator\Commands\Traits\ProcessJSON;

class CrudCommand extends Command
{
    use ProcessJSON;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:generate
                            {name : The name of the Crud.}
                            {--fields= : Field names for the form & migration.}
                            {--fields-from-file= : Fields from a json file.}
                            {--validations= : Validation rules for the fields.}
                            {--controller-namespace= : Namespace of the controller.}
                            {--foreign-keys= : The foreign keys for the table.}
                            {--relationships= : The relationships for the model.}
                            {--route=yes : Include Crud route to routes.php? yes|no.}
                            {--route-group= : Prefix of the route group.}
                            {--view-path= : The name of the view path.}
                            {--localize=no : Allow to localize? yes|no.}
                            {--locales=en : Locales language type.}
                            {--soft-deletes=no : Include soft deletes fields.}
                            {--type=web : The type of crud web or api.}
                            {--format-code=yes : Format code styles.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Crud including controller, model, views, requests & migrations.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = $this->argument('name');
        $fieldsFromFile = $this->option('fields-from-file');
        $viewPath = $this->option('view-path');
        $foreignKeys = $this->option('foreign-keys');
        $routeGroup = $this->option('route-group');
        $fields = rtrim($this->option('fields'), ';');
        $validations = trim($this->option('validations'));
        $localize = $this->option('localize');
        $type = $this->option('type');
        $locales = $this->option('locales');
        $relationships = $this->option('relationships');
        $softDeletes = $this->option('soft-deletes');

        $modelName = Str::singular($name);
        $tableName = Str::plural(Str::snake($name, '_'));

        if ($fieldsFromFile) {
            $fields = $this->processJSONFields($fieldsFromFile);

            $foreignKeys = $this->processJSONForeignKeys($fieldsFromFile);

            $validations = $this->processJSONValidations($fieldsFromFile);

            $relationships = $this->processJSONRelationships($fieldsFromFile);
        }

        [$fillable, $migrationFields] = $this->formatFields($fields);

        $this->call('crud:controller', [
            'name' => $name,
            '--model' => $modelName,
            '--view-path' => $viewPath,
            '--route-group' => $routeGroup,
            '--fields' => $fields,
            '--validations' => $validations,
            '--type' => $type,
        ]);
        $this->call('crud:model', [
            'name' => $modelName,
            '--fillable' => $fillable,
            '--table' => $tableName,
            '--relationships' => $relationships,
            '--soft-deletes' => $softDeletes,
        ]);
        $this->call('crud:migration', [
            'name' => $tableName,
            '--schema' => $migrationFields,
            '--foreign-keys' => $foreignKeys,
            '--soft-deletes' => $softDeletes,
        ]);

        if ($type == 'web') {
            $this->call('crud:view', [
                'name' => $name,
                '--fields' => $fields,
                '--validations' => $validations,
                '--view-path' => $viewPath,
                '--route-group' => $this->option('route-group'),
                '--localize' => $localize,
            ]);
        }

        if ($localize == 'yes') {
            $this->call('crud:lang', [
                'name' => $name,
                '--fields' => $fields,
                '--locales' => $locales,
            ]);
        }

        $this->setRoute($type, $name);

        // For optimizing the class loader
        $this->callSilent('optimize');
    }

    public function setRoute($type, $name)
    {
        $routeGroup = $this->option('route-group');

        $routeName = $routeGroup ?
            $routeGroup.'/'.Str::snake(Str::plural($name), '-')
            : Str::snake(Str::plural($name), '-');

        $controllerNamespace = $this->getDefaultControllerNamespace();
        $controller = ($controllerNamespace != '') ? $controllerNamespace.$name.'Controller' : $name.'Controller';

        // Set routes
        if ($type == 'web') {
            $this->addRoutesInFile('routes/web.php', $this->route('resource', $routeName, $controller));
        } else {
            $this->addRoutesInFile('routes/api.php', $this->route('apiResource', $routeName, $controller));
        }
    }

    /**
     * Get the default controller namespace for the class.
     *
     * @return string
     */
    protected function getDefaultControllerNamespace()
    {
        return ($this->option('controller-namespace')) ? $this->option('controller-namespace').'\\' : '';
    }

    /**
     * Format the fields.
     *
     * @return array
     */
    protected function formatFields($fields): array
    {
        $fieldsArray = explode(';', $fields);
        $fillableArray = [];
        $migrationFields = '';

        foreach ($fieldsArray as $item) {
            $spareParts = explode('#', trim($item));
            $fillableArray[] = $spareParts[0];
            $modifier = ! empty($spareParts[2]) ? $spareParts[2] : 'nullable';

            // Process migration fields
            $migrationFields .= $spareParts[0].'#'.$spareParts[1];
            $migrationFields .= '#'.$modifier;
            $migrationFields .= ';';
        }

        $commaSeparatedString = implode("', '", $fillableArray);
        $fillable = "['".$commaSeparatedString."']";

        return [
            $fillable,
            $migrationFields,
        ];
    }

    /**
     * Add route.
     *
     * @return  array
     */
    protected function route($type, $routeName, $controller)
    {
        return ['Route::'.$type."('".$routeName."', ".$controller.'::class'.');'];
    }

    /**
     * Add routes in file.
     *
     * @return  void
     */
    protected function addRoutesInFile($filePath, $routes)
    {
        $routeFile = base_path($filePath);

        if (file_exists($routeFile) && (strtolower($this->option('route')) === 'yes')) {
            $isAdded = File::append($routeFile, "\n".implode("\n", $routes));

            if ($isAdded) {
                $this->info('Crud/Resource route added to '.$filePath);
            } else {
                $this->info('Unable to add the route to '.$filePath);
            }
        }
    }
}
