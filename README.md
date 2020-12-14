# Laravel Nova CSV Import

[![Latest Stable Version](https://poser.pugx.org/simonhamp/laravel-nova-csv-import/v/stable)](https://packagist.org/packages/simonhamp/laravel-nova-csv-import)
[![Total Downloads](https://poser.pugx.org/simonhamp/laravel-nova-csv-import/downloads)](https://packagist.org/packages/simonhamp/laravel-nova-csv-import)
[![License](https://poser.pugx.org/simonhamp/laravel-nova-csv-import/license)](https://packagist.org/packages/simonhamp/laravel-nova-csv-import)

A simple CSV import tool for Laravel Nova. This package builds on top of the great work done by Sparclex with the [nova-import-card](https://github.com/Sparclex/nova-import-card) package.

![Laravel Nova CSV Import Screenshot](https://raw.githubusercontent.com/simonhamp/laravel-nova-csv-import/master/screenshots/readme.png)

## Installation

Install via Composer:

```bash
composer require simonhamp/laravel-nova-csv-import
```

Once installed, you must register the component in your app's `NovaServiceProvider` (`app/Providers/NovaServiceProvider.php`):

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
type |Option|Description|Default|
|-----|------|-----------|-------|
static | $canImportResource | set static boolean value to allow import to the Nova Resource | true
method | canImportResource | define the function to return boolean to allow import to the Nova Resource. Precede static value. | N/A
method | exceptAttributesImportResource | define the function to return attributes that would need not show up in selection of import | N/A  
  

### Example 
  
```php
// App\Nova\User
public static function canImportResource(Request $request)
{
    return $request->user()->can("create", self::$model);
}
```

## Importer Class 
This package uses [maatwebsite/excel](https://github.com/Maatwebsite/Laravel-Excel) behind the scenes to handle the actual import. You can find more information about how importing [works here](https://docs.laravel-excel.com/3.1/imports/basics.html#importing-basics)

You can define your own importer class by providing the relevant class name in your published copy of this package's config file.
  
Publish the config file 
```
php artisan vendor:publish --tag=nova-csv-import
``` 

define own importer class
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
