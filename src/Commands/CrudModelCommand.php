<?php

namespace MichaelNabil230\LaravelCrudGenerator\Commands;

use Illuminate\Console\GeneratorCommand;

class CrudModelCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:model
                            {name : The name of the model.}
                            {--table= : The name of the table.}
                            {--fillable= : The names of the fillable columns.}
                            {--relationships= : The relationships for the model}
                            {--soft-deletes=no : Include soft deletes fields.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new model.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Model';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return config('crud-generator.custom_template')
            ? config('crud-generator.path') . '/model.stub'
            : __DIR__ . '/../stubs/model.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return is_dir(app_path('Models')) ? $rootNamespace . '\\Models' : $rootNamespace;
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $table = $this->option('table') ?: $this->argument('name');
        $fillable = $this->option('fillable');

        $replace = [
            '{{table}}' => $table,
            '{{fillable}}' => $fillable,
            '{{relationships}}' => '',
        ];

        $replace = $this->buildSoftDelete($replace);
        // $replace = $this->buildRelationships($replace);

        return str_replace(
            array_keys($replace),
            array_values($replace),
            parent::buildClass($name)
        );
    }

    /**
     * Build the model class with the given name.
     *
     * @param  array  $replace
     *
     * @return array
     */
    protected function buildRelationships($replace)
    {
        $relationships = trim($this->option('relationships')) != '' ? explode(';', trim($this->option('relationships'))) : [];

        foreach ($relationships as $rel) {
            // relationshipname#relationshiptype#args_separated_by_pipes
            // e.g. employees#hasMany#App\Employee|id|dept_id
            // user is responsible for ensuring these relationships are valid
            $parts = explode('#', $rel);

            if (count($parts) != 3) {
                continue;
            }

            // blindly wrap each arg in single quotes
            $args = explode('|', trim($parts[2]));
            $argsString = '';
            foreach ($args as $k => $v) {
                if (trim($v) == '') {
                    continue;
                }

                $argsString .= "'" . trim($v) . "', ";
            }

            $argsString = substr($argsString, 0, -2); // remove last comma

            $replace = $this->createRelationshipFunction($replace, trim($parts[0]), trim($parts[1]), $argsString);
        }

        return array_merge($replace, [
            '{{relationships}}' => $replace,
        ]);
    }

    /**
     * Build the (optional) soft deletes part for the given stub.
     *
     * @param  array  $replace
     *
     * @return array
     */
    protected function buildSoftDelete($replace)
    {
        $softDelete = $this->option('soft-deletes');

        return array_merge($replace, [
            '{{softDeletes}}' => $softDelete == 'yes' ? "use SoftDeletes;\n    " : '',
            '{{useSoftDeletes}}' =>  $softDelete == 'yes' ? "use Illuminate\Database\Eloquent\SoftDeletes;\n" : '',
        ]);
    }

    /**
     * Create the code for a model relationship
     *
     * @param array $replace
     * @param string $relationshipName  the name of the function, e.g. owners
     * @param string $relationshipType  the type of the relationship, hasOne, hasMany, belongsTo etc
     * @param array $relationshipArgs   args for the relationship function
     */
    protected function createRelationshipFunction($replace, $relationshipName, $relationshipType, $argsString)
    {
        $tabIndent = '    ';
        $code = "public function " . $relationshipName . "()\n" . $tabIndent . "{\n" . $tabIndent . $tabIndent
            . "return \$this->" . $relationshipType . "(" . $argsString . ");"
            . "\n" . $tabIndent . "}";

        $str = '{{relationships}}';

        return array_merge($replace, [
            '{{relationships}}' => $code . "\n" . $tabIndent . $str,
        ]);
    }
}
