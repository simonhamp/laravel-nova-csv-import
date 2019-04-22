# Laravel Nova CSV Import

[![Latest Stable Version](https://poser.pugx.org/simonhamp/laravel-nova-csv-import/v/stable)](https://packagist.org/packages/simonhamp/laravel-nova-csv-import)
[![Total Downloads](https://poser.pugx.org/simonhamp/laravel-nova-csv-import/downloads)](https://packagist.org/packages/simonhamp/laravel-nova-csv-import)
[![License](https://poser.pugx.org/simonhamp/laravel-nova-csv-import/license)](https://packagist.org/packages/simonhamp/laravel-nova-csv-import)

A fully-fledged CSV import tool for Laravel Nova. This package builds on top of the great work done by Sparclex with the [nova-import-card](https://github.com/Sparclex/nova-import-card) package.

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

## Testing

We need tests! Can you help? Please consider contributing.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [LICENSE](LICENSE.md) for more details.