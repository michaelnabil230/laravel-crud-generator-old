<?php

namespace MichaelNabil230\LaravelCrudGenerator\Commands\Traits;

use Illuminate\Support\Facades\File;

trait ProcessJSON
{
    /**
     * Process the JSON Fields.
     *
     * @param  string  $file
     * @return string
     */
    protected function processJSONFields($file)
    {
        $json = File::get($file);
        $fields = json_decode($json);

        $fieldsString = '';
        foreach ($fields->fields as $field) {
            if ($field->type === 'select' || $field->type === 'enum') {
                $fieldsString .= $field->name.'#'.$field->type.'#options='.json_encode($field->options).';';
            } else {
                $fieldsString .= $field->name.'#'.$field->type.';';
            }
        }

        $fieldsString = rtrim($fieldsString, ';');

        return $fieldsString;
    }

    /**
     * Process the JSON Foreign keys.
     *
     * @param  string  $file
     * @return string
     */
    protected function processJSONForeignKeys($file)
    {
        $json = File::get($file);
        $fields = json_decode($json);

        if (! property_exists($fields, 'foreign_keys')) {
            return '';
        }

        $foreignKeysString = '';
        foreach ($fields->foreign_keys as $foreign_key) {
            $foreignKeysString .= $foreign_key->column.'#'.$foreign_key->references.'#'.$foreign_key->on;

            if (property_exists($foreign_key, 'onDelete')) {
                $foreignKeysString .= '#'.$foreign_key->onDelete;
            }

            if (property_exists($foreign_key, 'onUpdate')) {
                $foreignKeysString .= '#'.$foreign_key->onUpdate;
            }

            $foreignKeysString .= ',';
        }

        $foreignKeysString = rtrim($foreignKeysString, ',');

        return $foreignKeysString;
    }

    /**
     * Process the JSON Relationships.
     *
     * @param  string  $file
     * @return string
     */
    protected function processJSONRelationships($file)
    {
        $json = File::get($file);
        $fields = json_decode($json);

        if (! property_exists($fields, 'relationships')) {
            return '';
        }

        $relationsString = '';
        foreach ($fields->relationships as $relation) {
            $relationsString .= $relation->name.'#'.$relation->type.'#'.$relation->class.';';
        }

        $relationsString = rtrim($relationsString, ';');

        return $relationsString;
    }

    /**
     * Process the JSON Validations.
     *
     * @param  string  $file
     * @return string
     */
    protected function processJSONValidations($file)
    {
        $json = File::get($file);
        $fields = json_decode($json);

        if (! property_exists($fields, 'validations')) {
            return '';
        }

        $validationsString = '';
        foreach ($fields->validations as $validation) {
            $first = $validation->field.'#';

            if (is_array($validation->rules)) {
                $validationsString .= $first.implode('|', $validation->rules).';';
            } else {
                $validationsString .= $first.'#'.$validation->rules.';';
            }
        }

        $validationsString = rtrim($validationsString, ';');

        return $validationsString;
    }
}
