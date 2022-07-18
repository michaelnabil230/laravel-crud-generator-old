<?php

namespace MichaelNabil230\LaravelCrudGenerator\Commands;

use Illuminate\Console\GeneratorCommand;

class CrudMigrationCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:migration
                            {name : The name of the migration.}
                            {--schema= : The name of the schema.}
                            {--indexes= : The fields to add an index to.}
                            {--foreign-keys= : Foreign keys.}
                            {--soft-deletes=no : Include soft deletes fields.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new migration.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Migration';

    /**
     *  Migration column types collection.
     *
     * @var array
     */
    protected $typeLookup = [
        'char' => 'char',
        'date' => 'date',
        'datetime' => 'dateTime',
        'time' => 'time',
        'timestamp' => 'timestamp',
        'text' => 'text',
        'mediumtext' => 'mediumText',
        'longtext' => 'longText',
        'json' => 'json',
        'jsonb' => 'jsonb',
        'binary' => 'binary',
        'number' => 'integer',
        'integer' => 'integer',
        'bigint' => 'bigInteger',
        'mediumint' => 'mediumInteger',
        'tinyint' => 'tinyInteger',
        'smallint' => 'smallInteger',
        'boolean' => 'boolean',
        'decimal' => 'decimal',
        'double' => 'double',
        'float' => 'float',
        'enum' => 'enum',
    ];

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return config('crud-generator.custom_template')
            ? config('crud-generator.path') . '/migration.stub'
            : __DIR__ . '/../stubs/migration.stub';
    }

    /**
     * Get the full path to the migration.
     *
     * @param  string  $name
     *
     * @return string
     */
    protected function getPath($name)
    {
        $tableName = $this->argument('name');
        $datePrefix = date('Y_m_d_His');

        return database_path('/migrations/' . $datePrefix . '_create_' . $tableName . '_table.php');
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $tableName = $this->argument('name');

        $replace = [
            '{{tableName}}' => $tableName,
        ];

        $replace = $this->buildSchemaUp($replace);

        return str_replace(
            array_keys($replace),
            array_values($replace),
            parent::buildClass($name)
        );
    }

    /**
     * Build the schemaUp for the given stub.
     *
     * @param  array  $replace
     *
     * @return array
     */
    protected function buildSchemaUp($replace)
    {
        $fieldsToIndex = trim($this->option('indexes')) != '' ? explode(',', $this->option('indexes')) : [];
        $foreignKeys = trim($this->option('foreign-keys')) != '' ? explode(',', $this->option('foreign-keys')) : [];
        $schema = rtrim($this->option('schema'), ';');
        $tabIndent = '    ';
        $schemaFields = '';

        $data = $this->formatData($schema);
        $this->addFields($data, $schemaFields, $tabIndent);
        $this->addFieldsToIndex($fieldsToIndex, $schemaFields, $fieldsToIndex);
        $this->addForeignIds($foreignKeys, $schemaFields, $tabIndent);
        $softDeletesSnippets = $this->addSoftDeletes($tabIndent);

        return array_merge($replace, [
            '{{schemaFields}}' => $schemaFields,
            '{{softDeletes}}' => $softDeletesSnippets,
        ]);
    }

    protected function formatData($schema): array
    {
        $fields = explode(';', $schema);
        $data = [];

        if (!$schema) {
            return $data;
        }

        foreach ($fields as $index => $field) {
            $fieldArray = explode('#', $field);
            $data[$index]['name'] = trim($fieldArray[0]);
            $data[$index]['type'] = trim($fieldArray[1]);
            if (($data[$index]['type'] === 'select'
                    || $data[$index]['type'] === 'enum')
                && isset($fieldArray[2])
            ) {
                $options = trim($fieldArray[2]);
                $data[$index]['options'] = str_replace('options=', '', $options);
            }

            $data[$index]['modifier'] = '';

            $modifierLookup = [
                'comment',
                'default',
                'first',
                'nullable',
                'unsigned',
            ];

            if (isset($fieldArray[2]) && in_array(trim($fieldArray[2]), $modifierLookup)) {
                $data[$index]['modifier'] = "->" . trim($fieldArray[2]) . "()";
            }
        }

        return $data;
    }

    protected function addFields($data, &$schemaFields, $tabIndent)
    {
        foreach ($data as $item) {
            if (isset($this->typeLookup[$item['type']])) {
                $type = $this->typeLookup[$item['type']];

                if ($type === 'select' || $type === 'enum') {
                    $enumOptions = array_keys(json_decode($item['options'], true));
                    $enumOptionsStr = implode(",", array_map(function ($string) {
                        return '"' . $string . '"';
                    }, $enumOptions));
                    $schemaFields .= "\$table->" . $type . "('" . $item['name'] . "', [" . $enumOptionsStr . "])";
                } else {
                    $schemaFields .= "\$table->" . $type . "('" . $item['name'] . "')";
                }
            } else {
                $schemaFields .= "\$table->string('" . $item['name'] . "')";
            }

            // Append column modifier
            $schemaFields .= $item['modifier'];
            $schemaFields .= ";\n" . $tabIndent . $tabIndent . $tabIndent;
        }
    }

    protected function addSoftDeletes($tabIndent)
    {
        $softDeletes = $this->option('soft-deletes');

        $softDeletesSnippets = $softDeletes == 'yes'
            ? "\$table->softDeletes();\n" . $tabIndent . $tabIndent . $tabIndent
            : '';

        return $softDeletesSnippets;
    }

    protected function addFieldsToIndex($fieldsToIndex, &$schemaFields, $tabIndent)
    {
        // add indexes and unique indexes as necessary
        foreach ($fieldsToIndex as $fldData) {
            $line = trim($fldData);

            // is a unique index specified after the #?
            // if no hash present, we append one to make life easier
            if (strpos($line, '#') === false) {
                $line .= '#';
            }

            // parts[0] = field name (or names if pipe separated)
            // parts[1] = unique specified
            $parts = explode('#', $line);
            if (strpos($parts[0], '|') !== 0) {
                $fieldNames = "['" . implode("', '", explode('|', $parts[0])) . "']"; // wrap single quotes around each element
            } else {
                $fieldNames = trim($parts[0]);
            }

            if (count($parts) > 1 && $parts[1] == 'unique') {
                $schemaFields .= "\$table->unique(" . trim($fieldNames) . ")";
            } else {
                $schemaFields .= "\$table->index(" . trim($fieldNames) . ")";
            }

            $schemaFields .= ";\n" . $tabIndent . $tabIndent . $tabIndent;
        }
    }

    protected function addForeignIds($foreignKeys, &$schemaFields, $tabIndent)
    {
        // foreign keys
        foreach ($foreignKeys as $fk) {
            $line = trim($fk);

            $parts = explode('#', $line);

            // if we don't have three parts, then the foreign key isn't defined properly
            // --foreign-keys="foreign_entity_id#id#foreign_entity#onDelete#onUpdate"
            if (count($parts) == 3) {
                $schemaFields .= "\$table->foreign('" . trim($parts[0]) . "')"
                    . "->references('" . trim($parts[1]) . "')->on('" . trim($parts[2]) . "')";
            } elseif (count($parts) == 4) {
                $schemaFields .= "\$table->foreign('" . trim($parts[0]) . "')"
                    . "->references('" . trim($parts[1]) . "')->on('" . trim($parts[2]) . "')"
                    . "->onDelete('" . trim($parts[3]) . "')" . "->onUpdate('" . trim($parts[3]) . "')";
            } elseif (count($parts) == 5) {
                $schemaFields .= "\$table->foreign('" . trim($parts[0]) . "')"
                    . "->references('" . trim($parts[1]) . "')->on('" . trim($parts[2]) . "')"
                    . "->onDelete('" . trim($parts[3]) . "')" . "->onUpdate('" . trim($parts[4]) . "')";
            } else {
                continue;
            }

            $schemaFields .= ";\n" . $tabIndent . $tabIndent . $tabIndent;
        }
    }
}
