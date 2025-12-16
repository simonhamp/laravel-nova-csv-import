<?php

namespace SimonHamp\LaravelNovaCsvImport;

use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool;

class LaravelNovaCsvImport extends Tool
{
    /**
     * Perform any tasks that need to happen when the tool is booted.
     *
     * @return void
     */
    public function boot()
    {
        // Nova 4: Uses Nova::script() directly
        Nova::script('laravel-nova-csv-import', __DIR__.'/../dist/js/tool.js');

        // Nova 5: Uses Nova::serving() with Nova::mix()
        // We check if the mix() method exists to maintain Nova 4 compatibility
        if (method_exists(Nova::class, 'mix')) {
            Nova::serving(function (): void {
                Nova::mix('laravel-nova-csv-import', __DIR__.'/../dist/mix-manifest.json');
            });
        }
    }

    /**
     * Build the view that renders the navigation links for the tool.
     *
     * @return \Illuminate\View\View
     */
    public function menu(Request $request)
    {
        return MenuSection::make('CSV Import')
            ->path('/csv-import')
            ->icon('upload');
    }
}
