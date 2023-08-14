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
        return "Use some of Laravel's <code>Str</code> helper functions.";
    }

    public function settings(): array
    {
        return [
            'function' => [
                'type' => 'select',
                'options' => [
                    'ascii' => 'ASCII only [ascii]',
                    'camel' => 'camelCase [camel]',
                    'kebab' => 'kebab-case [kebab]',
                    'lcfirst' => 'lower Case First [lcfirst]',
                    'lower' => 'lowercase [lower]',
                    'plural' => 'Pluralize [plural]',
                    'reverse' => 'esreveR [reverse]',
                    'singular' => 'Singularize [singular]',
                    'slug' => 'slug-ify [slug]',
                    'snake' => 'sname_case [snake]',
                    'squish' => 'Squish [squish]',
                    'title' => 'Title Case [title]',
                    'ucfirst' => 'Upper case first [ucfirst]',
                    'upper' => 'UPPERCASE [upper]',
                ],
                'help' => 'Find out more about these functions in the
                    <a href="https://laravel.com/docs/helpers#strings-method-list" target="_blank">Laravel documentation</a>.',
            ],
        ];
    }

    public function handle($value = null, array $settings = []): string
    {
        $function = $settings['function'];

        return LaravelStr::$function($value);
    }
}
