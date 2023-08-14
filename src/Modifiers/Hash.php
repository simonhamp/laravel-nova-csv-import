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
            'algorithm' => [
                'type' => 'select',
                'options' => hash_algos(),
                'help' => 'See the
                    <a href="https://www.php.net/manual/en/function.hash.php" target="_blank">PHP documentation</a>
                    for more details about hashing.'
            ]
        ];
    }

    public function handle($value = null, array $settings = []): string
    {
        return hash($settings['algorithm'], $value);
    }
}
