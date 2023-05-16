# Laravel Nova CSV Import

[![Latest Stable Version](https://poser.pugx.org/simonhamp/laravel-nova-csv-import/v/stable?style=for-the-badge)](https://packagist.org/packages/simonhamp/laravel-nova-csv-import) [![Total Downloads](https://poser.pugx.org/simonhamp/laravel-nova-csv-import/downloads?style=for-the-badge)](https://packagist.org/packages/simonhamp/laravel-nova-csv-import) [![License](https://poser.pugx.org/simonhamp/laravel-nova-csv-import/license?style=for-the-badge)](https://packagist.org/packages/simonhamp/laravel-nova-csv-import)

[![RINGER](https://www.ringerhq.com/images/get-support-on-ringer.svg)](https://www.ringerhq.com/i/simonhamp/laravel-nova-csv-import)

A simple CSV import tool for Laravel Nova that allows you to upload a CSV file via Nova and choose which Nova resource
to import it to.

The import process lets you choose how to map the relevant columns from your uploaded file to the attributes on your
models, with a nice summary at the end of what worked and what didn't

> This package builds on top of the great work done by Sparclex with the [nova-import-card](https://github.com/Sparclex/nova-import-card) package.

![Laravel Nova CSV Import Screenshot](https://raw.githubusercontent.com/simonhamp/laravel-nova-csv-import/master/screenshots/readme.png)

**NB: As of v0.4.0, CSV Import requires Nova v4 and above. For Nova versions prior to v4, please use a CSV Import v0.3.0 or lower.
Please also be aware that versions prior to v0.4.0 will no longer be maintained.**

## Sponsorship
CSV Import is completely free to use. I've built it in my own time to fill my own needs and I also support it in my own
time. If you'd like to show your appreciation for that, I do accept [donations via GitHub](https://github.com/sponsors/simonhamp).

Thank you 🙏

## Installation

Install via Composer:

```bash
composer require simonhamp/laravel-nova-csv-import --with-all-dependencies
```

Once installed, you must register the component in your app's `NovaServiceProvider`
(usually in `app/Providers/NovaServiceProvider.php`):

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

### If you have customised your Nova main menu

If you have [customised your main menu](https://nova.laravel.com/docs/4.0/customization/menus.html#customizing-the-main-menu),
then you will need to manually register the tool's menu item in your custom menu for it to appear.

For example, in your `app/Providers/NovaServiceProvider.php`:

```php
public function boot()
{
    parent::boot();

    Nova::mainMenu(function (Request $request) {
        return [
            // ... other custom menu items

            MenuSection::make('CSV Import')
                ->path('/csv-import')
                ->icon('upload'),
        ];
    }
}
```

## Options
By default, _all_ of your Nova Resources will be available for import. However, there are a number of ways that you can
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
php artisan vendor:publish --tag=csv-import
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
