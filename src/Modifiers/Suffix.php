<?php

namespace SimonHamp\LaravelNovaCsvImport\Modifiers;

use Illuminate\Support\Str as LaravelStr;
use SimonHamp\LaravelNovaCsvImport\Contracts\Modifier;

class Suffix implements Modifier
{
    public function title(): string
    {
        return 'Suffix value';
    }

    public function description(): string
    {
        return "Suffix each row with a given string";
    }

    public function settings(): array
    {
        return [
            'string' => [
                'type' => 'string',
                'title' => 'Suffix',
            ],
        ];
    }

    public function handle($value = null, array $settings = []): string
    {
        return LaravelStr::finish($value, $settings['string']);
    }
}
