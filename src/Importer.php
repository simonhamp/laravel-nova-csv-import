<?php

namespace SimonHamp\LaravelNovaCsvImport;

use Laravel\Nova\Resource;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithMapping;

class Importer implements ToModel, WithValidation, WithHeadingRow, WithMapping, WithBatchInserts, WithChunkReading, SkipsOnFailure, SkipsOnError
{
    use Importable, SkipsFailures, SkipsErrors;

    /** @var Resource */
    protected $resource;
    protected $attribute_map;
    protected $rules;
    protected $model_class;

    public function map($row): array
    {
        if (! $this->attribute_map) {
            return $row;
        }

        $data = [];

        foreach ($this->attribute_map as $attribute => $column) {
            if (! $column) {
                continue;
            }

            $data[$attribute] = $this->preProcessValue($row[$column]);
        }

        return $data;
    }

    public function model(array $row)
    {
        $model = $this->resource::newModel();

        $model->fill($row);

        return $model;
    }

    public function rules(): array
    {
        return $this->rules;
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    /**
     * @return mixed
     */
    public function getAttributeMap()
    {
        return $this->attribute_map;
    }

    /**
     * @param mixed $map
     * @return Importer
     */
    public function setAttributeMap($map)
    {
        $this->attribute_map = $map;

        return $this;
    }

    /**
     * @param mixed $rules
     * @return Importer
     */
    public function setRules($rules)
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getModelClass()
    {
        return $this->model_class;
    }

    /**
     * @param mixed $model_class
     * @return Importer
     */
    public function setModelClass($model_class)
    {
        $this->model_class = $model_class;

        return $this;
    }

    public function setResource($resource)
    {
        $this->resource = $resource;

        return $this;
    }

    private function preProcessValue($value)
    {
        switch ($value) {
            case 'FALSE':
                return false;
                break;
            case 'TRUE':
                return true;
                break;
        }

        return $value;
    }
}
