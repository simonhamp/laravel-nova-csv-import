<?php

namespace SimonHamp\LaravelNovaCsvImport;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use Maatwebsite\Excel\Concerns\ToModel as ModelImporter;
use SimonHamp\LaravelNovaCsvImport\Http\Controllers\ImportController;
use SimonHamp\LaravelNovaCsvImport\Http\Controllers\UploadController;
use SimonHamp\LaravelNovaCsvImport\Http\Middleware\Authorize;

class ToolServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->booted(function () {
            $this->routes();
        });

        Nova::serving(function (ServingNova $event) {
        });

        $this->publishes([
            __DIR__.'/../config/csv-import.php' => config_path('csv-import.php'),
        ], 'csv-import');
    }

    /**
     * Register the tool's routes.
     *
     * @return void
     */
    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Nova::router(['nova', Authorize::class], 'csv-import')
            ->namespace(__NAMESPACE__.'\\Http\\Controllers')
            ->group(__DIR__.'/../routes/inertia.php');

        Route::middleware(['nova', Authorize::class])
            ->namespace(__NAMESPACE__.'\\Http\\Controllers')
            ->prefix('nova-vendor/laravel-nova-csv-import')
            ->group(__DIR__.'/../routes/api.php');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/csv-import.php', 'csv-import');

        $this->app->when([UploadController::class, ImportController::class])
            ->needs(Filesystem::class)
            ->give(function () {
                return Storage::disk(config('csv-import.disk'));
            });

        $this->app->when([UploadController::class, ImportController::class])
            ->needs(ModelImporter::class)
            ->give(function () {
                $class = $this->app['config']->get('csv-import.importer');

                $importable = \Maatwebsite\Excel\Concerns\Importable::class;

                if (! in_array($importable, class_uses($class))) {
                    throw new \Exception("Importer [{$class}] must use the Importable trait: {$importable}");
                }

                return $this->app->make($class);
            });
    }
}
