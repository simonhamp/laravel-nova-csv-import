<?php

namespace SimonHamp\LaravelNovaCsvImport\Http\Controllers;

use Laravel\Nova\Nova;
use Laravel\Nova\Resource;
use Laravel\Nova\Rules\Relatable;
use Laravel\Nova\Actions\ActionResource;
use Laravel\Nova\Http\Requests\NovaRequest;
use SimonHamp\LaravelNovaCsvImport\Importer;
use Illuminate\Validation\ValidationException;
use Laravel\Nova\Fields\Field;
use Illuminate\Support\Facades\Storage;

class ImportController
{
    /**
     * @var Importer
     */
    protected $importer;

    public function __construct()
    {
        $class = config('nova-csv-importer.importer');
        $this->importer = new $class;
    }

    public function configure(NovaRequest $request, $file)
    {
        $import = $this->importer
            ->toCollection($this->getFilePath($file), null)
            ->first();

        $headings = $import->first()->keys();

        $total_rows = $import->count();

        $config = $this->getConfigForFile($file);

        $rows = $import->take(10)->all();

        $resources = $this->getAvailableResourcesForImport($request); 

        $fields = $resources->mapWithKeys(function ($resource) use ($request) {
            return $this->getAvailableFieldsForImport($resource, $request);
        });

        $resources = $resources->mapWithKeys(function ($resource) {
            return [$resource::uriKey() => $resource::label()];
        });

        return inertia('CsvImport/Configure', compact('file', 'resources', 'fields', 'rows', 'total_rows', 'headings', 'config'));
    }

    public function storeConfig(NovaRequest $request)
    {
        $config = json_encode([
            'resource' => $request->input('resource'),
            'map' => $request->input('map'),
        ]);

        $file = $request->input('file');

        Storage::put("csv-import/{$file}.config.json", $config);
    }

    public function preview(NovaRequest $request, $file)
    {
        $import = $this->importer
            ->toCollection($this->getFilePath($file), null)
            ->first();

        $total_rows = $import->count();

        $config = $this->getConfigForFile($file);

        $columns = $config['map'];
        $resource = $config['resource'];

        $mapped_columns = array_values(array_filter($config['map']));

        $rows = $import->take(100)->all();

        return inertia('CsvImport/Preview', compact('config', 'rows', 'total_rows', 'columns', 'mapped_columns', 'resource', 'file'));
    }

    public function import(NovaRequest $request)
    {
        $file = $request->input('file');

        Storage::delete("csv-import/{$file}.results.json");

        $config = $this->getConfigForFile($file);
        $resource_name = $config['resource'];
        $attribute_map = $config['map'];

        $resource = Nova::resourceInstanceForKey($resource_name);
        $attributes = $resource->creationFields($request)->pluck('attribute');
        $rules = $this->extractValidationRules($request, $resource)->toArray();
        $model_class = get_class($resource->resource);

        $import = $this->importer
            ->toCollection($this->getFilePath($file), null)
            ->first();

        $total_rows = $import->count();

        $this->importer
            ->setResource($resource)
            ->setAttributeMap($attribute_map)
            ->setRules($rules)
            ->setModelClass($model_class)
            ->import($this->getFilePath($file), null);

        $failures = $this->importer->failures();
        $errors = $this->importer->errors();

        Storage::put("csv-import/{$file}.results.json", json_encode([
            'total_rows' => $total_rows,
            'imported' => $total_rows - $failures->count() - $errors->count(),
            'failures' => $failures,
            'errors' => $errors,
        ]));

        return response()->json(['review' => "/csv-import/review/{$file}"]);
    }

    public function review(NovaRequest $request, $file)
    {
        $results = $this->getLastResultsForFile($file);

        $imported = $results['imported'];
        $total_rows = $results['total_rows'];
        $failures = collect($results['failures'])->groupBy('row');
        $errors = collect($results['errors'])->groupBy('row');

        $config = $this->getConfigForFile($file);

        return inertia('CsvImport/Review', compact('file', 'failures', 'errors', 'total_rows', 'config', 'imported'));
    }

    protected function getAvailableFieldsForImport(String $resource, $request)
    {
        $novaResource = new $resource(new $resource::$model);
        $fieldsCollection = collect($novaResource->creationFields($request));

        if (method_exists($novaResource, 'excludeAttributesFromImport')) {
            $fieldsCollection = $fieldsCollection->filter(function(Field $field) use ($novaResource, $request) {
                return !in_array($field->attribute, $novaResource::excludeAttributesFromImport($request));
            });
        }

        $fields = $fieldsCollection->map(function (Field $field) {
            return [
                'name' => $field->name,
                'attribute' => $field->attribute
            ];
        });
        
        // Note: ->values() is used here to avoid this array being turned into an object due to 
        // non-sequential keys (which might happen due to the filtering above.
        return [$novaResource->uriKey() => $fields->values()];
    }

    protected function getAvailableResourcesForImport(NovaRequest $request) {

        $novaResources = collect(Nova::authorizedResources($request));

        return $novaResources->filter(function ($resource) use ($request) {
            if ($resource === ActionResource::class) {
                return false;
            }

            if (!isset($resource::$model)) {
                return false;
            }
            
            $resourceReflection = (new \ReflectionClass((string) $resource));
            
            if ($resourceReflection->hasMethod('canImportResource')) {
                return $resource::canImportResource($request);
            }

            $static_vars = $resourceReflection->getStaticProperties();

            if (!isset($static_vars['canImportResource'])) {
                return true;
            }

            return isset($static_vars['canImportResource']) && $static_vars['canImportResource'];
        });
    }

    protected function extractValidationRules($request, Resource $resource)
    {
        return collect($resource::rulesForCreation($request))->mapWithKeys(function ($rule, $key) {
            foreach ($rule as $i => $r) {
                if (! is_object($r)) {
                    continue;
                }

                // Make sure relation checks start out with a clean query
                if (is_a($r, Relatable::class)) {
                    $rule[$i] = function () use ($r) {
                        $r->query = $r->query->newQuery();
                        return $r;
                    };
                }
            }

            return [$key => $rule];
        });
    }

    protected function getConfigForFile($file)
    {
        return json_decode(Storage::get("csv-import/{$file}.config.json"), true);
    }

    protected function getLastResultsForFile($file)
    {
        return json_decode(Storage::get("csv-import/{$file}.results.json"), true);
    }

    protected function getFilePath($file)
    {
        return storage_path("app/csv-import/{$file}");
    }
}
