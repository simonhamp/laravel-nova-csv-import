<?php

namespace SimonHamp\LaravelNovaCsvImport\Http\Requests;

use Laravel\Nova\Http\Requests\NovaRequest;

class ImportNovaRequest extends NovaRequest
{
    /**
     * @var
     */
    protected $importResource;

    /**
     * @param mixed $resource
     */
    public function setImportResource($resource): self
    {
        $this->importResource = $resource;

        return $this;
    }

    /**
     * Get the class name of the resource being requested.
     *
     * @return class-string<\Laravel\Nova\Resource>
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function resource()
    {
        return tap(once(function () {
            return $this->importResource;
        }), function ($resource) {
            abort_if(is_null($resource), 404);
        });
    }
}