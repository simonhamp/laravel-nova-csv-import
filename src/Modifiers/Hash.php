<?php

namespace SimonHamp\LaravelNovaCsvImport\Modifiers;

use SimonHamp\LaravelNovaCsvImport\Contracts\Modifier;

class Hash implements Modifier
{
    public function title(): string
    {
        return 'Hash value';
    }

    public function description(): string
    {
        return 'Hash the value using the given hashing algorithm.';
    }

    public function settings(): array
    {
        return [
            'algorithm' => hash_algos(),
        ];
    }

    public function handle($value = null, array $settings = []): string
    {
        return hash($settings['algorithm'], $value);
    }
}
