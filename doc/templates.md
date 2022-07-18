## Custom Templates

The package allows user to extensively customize or use own templates.

### All Templates

To customize or change the template, you need to follow these steps:

1. Just make sure you've published all assets of this package. If you didn't just run this command.
    ```php
    php artisan vendor:publish --provider="MichaelNabil230\LaravelCrudGenerator\CrudGeneratorServiceProvider"
    ```
2. To override the default template with yours, turn on `custom_template` option in the `config/crud-generator.php` file.
    ```php
    'custom_template' => true,
    ```

3. Now you can customize everything from this `resources/crud-generator/` directory.

4. Even if you need to use any custom variable just add those in the `config/crud-generator.php` file.

[&larr; Back to index](README.md)
