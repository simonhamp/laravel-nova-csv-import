<?php

namespace SimonHamp\LaravelNovaCsvImport\Http\Controllers;

use App\Nova\Resource;
use Illuminate\Database\QueryException;
use Laravel\Nova\Nova;
use Laravel\Nova\Rules\Relatable;
use Laravel\Nova\Resource as NovaResource;
use Laravel\Nova\Http\Requests\NovaRequest;
use SimonHamp\LaravelNovaCsvImport\Importer;
use Illuminate\Validation\ValidationException;
use SimonHamp\LaravelNovaCsvImport\ImportException;
use Maatwebsite\Excel\Exceptions\NoTypeDetectedException;

class ImportController
{
    /**
     * @var Importer
     */
    protected $importer;

    public function __construct()
    {
        $class = config('laravel-nova-csv-import.importer');

        $this->importer = new $class;
    }

    public function preview(NovaRequest $request, $file)
    {
        $import = $this->importer
            ->toCollection($this->getFilePath($file), null, 'Csv')
            ->first();

        $headings = $import->first()->keys();

        $total_rows = $import->count();

        $sample = $import->take(10)->all();

        $resources = collect(Nova::$resources);

        $fields = $resources->map(function ($resource) {
            $model = $resource::$model;

            return new $resource(new $model);
        })->mapWithKeys(function (Resource $resource) use ($request) {
            return [$resource->uriKey() => $resource->creationFields($request)];
        });

        $resources = $resources->mapWithKeys(function ($resource) {
            return [$resource::uriKey() => $resource::label()];
        });

        return response()->json(compact('sample', 'resources', 'fields', 'total_rows', 'headings'));
    }

    public function import(NovaRequest $request, $file)
    {
        $resource_name = $request->input('resource');
        $request->route()->setParameter('resource', $resource_name);

        $resource = Nova::resourceInstanceForKey($resource_name);
        $attribute_map = $request->input('mappings');
        $attributes = $resource->creationFields($request)->pluck('attribute');
        $rules = $this->extractValidationRules($request, $resource)->toArray();
        $model_class = get_class($resource->resource);

        $this->importer
            ->setResource($resource)
            ->setAttributes($attributes)
            ->setAttributeMap($attribute_map)
            ->setRules($rules)
            ->setModelClass($model_class);

        try {
            $this->importer->import($this->getFilePath($file), null, 'Csv');
            throw new \Exception($e->getPrevious()->errorInfo[2]);
        } catch (QueryException $e) {
        } catch (ImportException $e) {
            $this->responseError($e->getMessage());
        } catch (NoTypeDetectedException $e) {
            $this->responseError(__('Invalid file type'));
        }

        return response()->json(['result' => 'success', ]);
    }

    protected function extractValidationRules($request, NovaResource $resource)
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

    protected function getFilePath($file)
    {
        return storage_path("nova/laravel-nova-import-csv/tmp/{$file}");
    }

    private function responseError($error)
    {
        throw ValidationException::withMessages([
            0 => [$error],
        ]);
    }
}
