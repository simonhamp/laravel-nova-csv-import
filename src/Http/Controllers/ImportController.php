<?php

namespace SimonHamp\LaravelNovaCsvImport\Http\Controllers;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Inertia\Response;
use Laravel\Nova\Actions\ActionResource;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;
use Laravel\Nova\Resource;
use Laravel\Nova\Rules\Relatable;
use Maatwebsite\Excel\Concerns\ToModel as ModelImporter;
use SimonHamp\LaravelNovaCsvImport\Http\Requests\ImportNovaRequest;


class ImportController
{
    protected $importer;

    protected $filesystem;

    public function __construct(ModelImporter $importer, Filesystem $filesystem)
    {
        $this->importer = $importer;

        $this->filesystem = $filesystem;
    }

    public function configure(ImportNovaRequest $request, string $file): Response
    {
        $file_name = pathinfo($file, PATHINFO_FILENAME);

        $import = $this->importer
            ->toCollection($this->getFilePath($file), $this->getDisk())
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
            return [
                $resource::uriKey() => $resource::label(),
            ];
        });

        $mods = $this->importer->getAvailableModifiers();

        return inertia(
            'CsvImport/Configure',
            compact('file', 'file_name', 'resources', 'fields', 'rows', 'total_rows', 'headings', 'config', 'mods')
        );
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function storeConfig(NovaRequest $request)
    {
        $file = $request->input('file');

        // TODO: Add some validation
        $config = json_encode(
            array_merge(
                $this->getConfigForFile($file),
                [
                    'resource' => $request->input('resource'),
                    'mappings' => $request->input('mappings'),
                    'values' => $request->input('values'),
                    'modifiers' => collect($request->input('modifiers'))
                        ->map(function ($modifiers) {
                            return collect($modifiers)
                                ->reject(function ($modifier) {
                                    return empty($modifier['name']);
                                });
                        }),
                ],
            ),
            JSON_PRETTY_PRINT
        );

        $path = $this->getConfigFilePath($file);

        $this->filesystem->delete($path);

        if (! $this->filesystem->put($path, $config)) {
            return abort(500);
        }
    }

    public function preview(NovaRequest $request, string $file): Response
    {
        $config = $this->getConfigForFile($file);

        $resource = $config['resource'];

        $import = $this->importer
            ->setAttributeMap($columns = $config['mappings'])
            ->setCustomValues($config['values'])
            ->setMeta($config['meta'])
            ->setModifiers($config['modifiers'])
            ->toCollection($this->getFilePath($file), $this->getDisk())
            ->first();

        $total_rows = $import->count();

        $mapped_columns = array_values(array_filter($columns));

        $rows = $import->take(100)->all();

        return inertia(
            'CsvImport/Preview',
            compact('config', 'rows', 'total_rows', 'columns', 'mapped_columns', 'resource', 'file')
        );
    }

    public function import(ImportNovaRequest $request)
    {
        $file = $request->input('file');

        $path = $this->getFilePath($file);

        if (! $config = $this->getConfigForFile($file)) {
            return redirect()->route('csv-import.configure', ['file' => $file]);
        }

        $resource_name = $config['resource'];

        $resource = Nova::resourceInstanceForKey($resource_name);
        $request->setImportResource(get_class($resource));
        $rules = $this->extractValidationRules($resource, $request)->toArray();
        $model_class = $resource->resource::class;

        $import = $this->importer
            ->toCollection($path, $this->getDisk())
            ->first();

        $total_rows = $import->count();

        $this->importer
            ->setResource($resource)
            ->setAttributeMap($config['mappings'])
            ->setRules($rules)
            ->setModelClass($model_class)
            ->setMeta($config['meta'])
            ->setCustomValues($config['values'])
            ->setModifiers($config['modifiers'])
            ->import($path, $this->getDisk());

        $failures = $this->importer->failures();
        $errors = $this->importer->errors();

        $results = $this->getResultsFilePath($file);

        $this->filesystem->delete($results);

        $this->filesystem->put($results, json_encode([
            'total_rows' => $total_rows,
            'imported' => $total_rows - $failures->count() - $errors->count(),
            'failures' => $failures,
            'errors' => $errors,
        ], JSON_PRETTY_PRINT));

        return response()->json(['review' => "/csv-import/review/{$file}"]);
    }

    public function review(NovaRequest $request, string $file): Response
    {
        if (! $results = $this->getLastResultsForFile($file)) {
            return redirect()->route('csv-import.preview', ['file' => $file]);
        }

        $imported = $results['imported'];
        $total_rows = $results['total_rows'];
        $failures = collect($results['failures'])->groupBy('row');
        $errors = collect($results['errors'])->groupBy('row');

        $config = $this->getConfigForFile($file);

        return inertia(
            'CsvImport/Review',
            compact('file', 'failures', 'errors', 'total_rows', 'config', 'imported')
        );
    }

    protected function getAvailableFieldsForImport(string $resource, ImportNovaRequest $request): array
    {
        try {
            $novaResource = new $resource(new $resource::$model);
            $fieldsCollection = collect($novaResource->creationFields($request));

            if (method_exists($novaResource, 'excludeAttributesFromImport')) {
                $fieldsCollection = $fieldsCollection->filter(function(Field $field) use ($novaResource, $request) {
                    return !in_array($field->attribute, $novaResource::excludeAttributesFromImport($request));
                });
            }

            $fields = $fieldsCollection->map(function (Field $field) use ($novaResource, $request) {
                $request->setImportResource($novaResource);
                return [
                    'name' => $field->name,
                    'attribute' => $field->attribute,
                    'rules' => $this->extractValidationRules($novaResource, $request)->get($field->attribute),
                ];
            });
        } catch (\Throwable $th) {
            return [];
        }

        // Note: ->values() is used here to avoid this array being turned into an object due to
        // non-sequential keys (which might happen due to the filtering above.
        return [
            $novaResource->uriKey() => $fields->values(),
        ];
    }

    protected function getAvailableResourcesForImport(NovaRequest $request): Collection
    {
        $novaResources = collect(Nova::authorizedResources($request));

        return $novaResources->filter(function ($resource) use ($request) {
            if ($resource === ActionResource::class) {
                return false;
            }

            if (! isset($resource::$model)) {
                return false;
            }

            $resourceReflection = (new \ReflectionClass((string) $resource));

            if ($resourceReflection->hasMethod('canImportResource')) {
                return $resource::canImportResource($request);
            }

            $static_vars = $resourceReflection->getStaticProperties();

            if (! isset($static_vars['canImportResource'])) {
                return true;
            }

            return isset($static_vars['canImportResource']) && $static_vars['canImportResource'];
        });
    }

    protected function extractValidationRules(Resource $resource, NovaRequest $request): Collection
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

    protected function getConfigForFile(string $file): array
    {
        $config = $this->getDataFromJsonFile($this->getConfigFilePath($file));

        $config['values'] = $config['values'] ?? [];
        $config['modifiers'] = $config['modifiers'] ?? new \stdClass;

        $original_filename = $config['original_filename'] ?? '';

        $config['meta'] = [
            'file' => $file,
            'file_name' => pathinfo($file, PATHINFO_FILENAME),
            'original_file' => $original_filename,
            'original_file_name' => pathinfo($original_filename, PATHINFO_FILENAME),
        ];

        return $config;
    }

    protected function getLastResultsForFile(string $file): array
    {
        return $this->getDataFromJsonFile($this->getResultsFilePath($file));
    }

    protected function getFilePath(string $file): string
    {
        return "csv-import/{$file}";
    }

    protected function getConfigFilePath(string $file): string
    {
        return $this->getFilePath("{$file}.config.json");
    }

    protected function getResultsFilePath(string $file): string
    {
        return $this->getFilePath("{$file}.results.json");
    }

    protected function getDataFromJsonFile(string $file): array
    {
        if ($this->filesystem->exists($file)) {
            return @json_decode($this->filesystem->get($file), true) ?? [];
        }

        return [];
    }

    protected function getDisk(): ?string
    {
        return config('csv-import.disk');
    }
}
