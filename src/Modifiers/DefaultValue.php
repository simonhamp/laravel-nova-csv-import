<?php

namespace SimonHamp\LaravelNovaCsvImport\Modifiers;

use SimonHamp\LaravelNovaCsvImport\Contracts\Modifier;

class DefaultValue implements Modifier
{
    public function title(): string
    {
        return 'Default Value';
    }

    public function description(): string
    {
        return 'Set a default value for the field if the CSV column is empty or missing';
    }

    public function settings(): array
    {
        return [
            'string' => [
                'type' => 'string',
                'title' => 'Default Value',
            ],
        ];
    }

    public function handle($value = null, array $settings = []): string
    {
        return $value === null ? $settings['string'] : $value;
    }
}
