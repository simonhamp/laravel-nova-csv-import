# Laravel Nova CSV Import

[![Latest Stable Version](https://poser.pugx.org/simonhamp/laravel-nova-csv-import/v/stable)](https://packagist.org/packages/simonhamp/laravel-nova-csv-import)
[![Total Downloads](https://poser.pugx.org/simonhamp/laravel-nova-csv-import/downloads)](https://packagist.org/packages/simonhamp/laravel-nova-csv-import)
[![License](https://poser.pugx.org/simonhamp/laravel-nova-csv-import/license)](https://packagist.org/packages/simonhamp/laravel-nova-csv-import)

A simple CSV import tool for Laravel Nova. This package builds on top of the great work done by Sparclex with the [nova-import-card](https://github.com/Sparclex/nova-import-card) package.

![Laravel Nova CSV Import Screenshot](https://raw.githubusercontent.com/simonhamp/laravel-nova-csv-import/master/screenshots/readme.png)

## Installation

Install via Composer:

```bash
composer require simonhamp/laravel-nova-csv-import --with-all-dependencies
```

Once installed, you must register the component in your app's `NovaServiceProvider`
(`app/Providers/NovaServiceProvider.php`):

```php
namespace App\Providers;

use SimonHamp\LaravelNovaCsvImport\LaravelNovaCsvImport;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    public function tools()
    {
        return [
            new LaravelNovaCsvImport,
        ];
    }
}
```

## Options
By default, all of your Nova Resources will be available for import. However, there are a number of ways that you can
explicitly limit what's available for importing.

`public static $canImportResource = false;`  
*Default:* `true`  
Add this static property to your Resource to prevent it from showing up in the Nova CSV Import tool interface.

`public static function canImportResource($request): bool`  
Define a `canImportResource` method to use more complex logic to decide if this Resource can be shown during import.
If defined, this takes precedence over the `$canImportResource` property.

`public static function excludeAttributesFromImport(): array`  
*Default:* `[]`  
Define a `excludeAttributesFromImport` method that returns an array of attribute names that you want to _exclude_ from
being visible in the import tool for this Resource.
  

### Example 
  
```php
// App\Nova\User
public static function canImportResource(Request $request)
{
    return $request->user()->can("create", self::$model);
}

public static function excludeAttributesFromImport()
{
    return ['password'];
}
```

## Importer Class 
This package uses [maatwebsite/excel](https://github.com/Maatwebsite/Laravel-Excel) behind the scenes to handle the
actual import. You can find more information about how importing
[works here](https://docs.laravel-excel.com/3.1/imports/basics.html#importing-basics).

You can define your own importer class by providing the relevant class name in your published copy of this package's
config file.
  
First, publish the config file:
```
php artisan vendor:publish --tag=nova-csv-import
``` 

Then, define and register your own importer class:
```
<?php

return [
    'importer' =>  App\Utilities\Importer::class,
];
```
## Testing

We need tests! Can you help? Please consider contributing.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [LICENSE](LICENSE.md) for more details.
