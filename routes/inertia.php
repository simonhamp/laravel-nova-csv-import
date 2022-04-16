<?php

use Illuminate\Support\Facades\Route;
use Laravel\Nova\Http\Requests\NovaRequest;

/*
|--------------------------------------------------------------------------
| Tool Routes
|--------------------------------------------------------------------------
|
| Here is where you may register Inertia routes for your tool. These are
| loaded by the ServiceProvider of the tool. The routes are protected
| by your tool's "Authorize" middleware by default. Now - go build!
|
*/

Route::get('/', function (NovaRequest $request) {
    return inertia('CsvImport/Main');
})->name('csv-import.home');

Route::get('/configure/{file}', 'ImportController@configure')->name('csv-import.configure');

Route::get('/preview/{file}', 'ImportController@preview')->name('csv-import.preview');

Route::get('/review', function (NovaRequest $request) {
    return inertia('CsvImport/Review');
})->name('csv-import.review');
