<?php

namespace SimonHamp\LaravelNovaCsvImport\Http\Controllers;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\Importable;
use Laravel\Nova\Http\Requests\NovaRequest;
use Maatwebsite\Excel\Concerns\ToModel as ModelImporter;

class UploadController
{
    use Importable;
    protected $importer;

    protected $filesystem;

    public function __construct(ModelImporter $importer, Filesystem $filesystem)
    {
        $this->importer = $importer;

        $this->filesystem = $filesystem;
    }
    {
        $data = Validator::make($request->all(), [
            'file' => 'required|file',
        ])->validate();

        $file = $request->file('file');

        try {
            $this->importer->toCollection($file);
        } catch (\Exception $e) {
            Log::error('Failed to parse uploaded file', [$e]);
            
            return response()->json(['message' => 'Sorry, we could not import that file'], 422);
        }

        // Store the file temporarily
        $hash = File::hash($file->getRealPath()).".".$extension;

        $this->filesystem->putFileAs('csv-import', $file, $newFile);

        return response()->json(['configure' => "/csv-import/configure/{$hash}"]);
    }
}
