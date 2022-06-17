<?php

return [
    'importer' => SimonHamp\LaravelNovaCsvImport\Importer::class,

    'disk' => null,

    "importable_by_default" => false,

    "exclude_attributes_global" => ["id", "created_at", "updated_at", "deleted_at"]
];
