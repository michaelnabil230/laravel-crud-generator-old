<?php

namespace MichaelNabil230\LaravelCrudGenerator\Tests;

use MichaelNabil230\LaravelCrudGenerator\Tests\TestCase;

class CrudGeneratorTest extends TestCase
{
    public function test_crud_generate_command()
    {
        // $this->artisan('crud:generate', [
        //     'name' => 'Posts',
        //     '--fields' => "title#string; content#text; category#select#options=technology,tips,health",
        // ]);
        // $this->assertContains('Controller already exists!', $this->consoleOutput());
    }

    public function test_controller_generate_command()
    {
        $this->artisan('crud:controller', [
            'name' => 'CustomersController',
            '--model' => 'Customer',
        ]);

        $this->assertContains('Controller created successfully.', $this->consoleOutput());

        $this->assertFileExists(app_path('Http/Controllers/CustomersController.php'));
    }

    public function test_model_generate_command()
    {
        $this->artisan('crud:model', [
            'name' => 'Customer',
            '--fillable' => "['name', 'email']",
        ]);

        $this->assertContains('Model created successfully.', $this->consoleOutput());

        $this->assertFileExists(app_path('Models/Customer.php'));
    }

    public function test_migration_generate_command()
    {
        $this->artisan('crud:migration', [
            'name' => 'customers',
            '--schema' => 'name#string; email#email',
        ]);

        $this->assertContains('Migration created successfully.', $this->consoleOutput());
    }

    public function test_view_generate_command()
    {
        $this->artisan('crud:view', [
            'name' => 'customers',
            '--fields' => "title#string; body#text",
        ]);

        $this->assertContains('View created successfully.', $this->consoleOutput());

        $this->assertDirectoryExists(config('view.paths')[0] . '/customers');
    }
}
