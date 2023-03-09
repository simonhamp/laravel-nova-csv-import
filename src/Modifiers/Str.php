<?php

namespace SimonHamp\LaravelNovaCsvImport\Modifiers;

use Illuminate\Support\Str as LaravelStr;
use SimonHamp\LaravelNovaCsvImport\Contracts\Modifier;

class Str implements Modifier
{
    public function title(): string
    {
        return 'String modifiers';
    }

    public function description(): string
    {
        return "Use some of Laravel's `Str` helper functions.";
    }

    public function settings(): array
    {
        return [
            'function' => [
                'type' => 'select',
                'options' => [
                    'ascii' => 'ASCII only',
                    'camel' => 'camelCase',
                    'kebab' => 'kebab-case',
                    'lcfirst' => 'lower Case First',
                    'lower' => 'lowercase',
                    'plural' => 'Pluralize',
                    'reverse' => 'esreveR',
                    'singular' => 'Singularize',
                    'slug' => 'slug-ify',
                    'snake' => 'sname_case',
                    'squish' => 'Squish',
                    'title' => 'Title Case',
                    'ucfirst' => 'Upper case first',
                    'upper' => 'UPPERCASE',
                ],
            ],
        ];
    }

    public function handle($value = null, array $settings = []): string
    {
        $function = $settings['function'];

        return LaravelStr::$function($value);
    }
}
