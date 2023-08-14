<?php

namespace SimonHamp\LaravelNovaCsvImport\Modifiers;

use Illuminate\Support\Facades\Hash;
use SimonHamp\LaravelNovaCsvImport\Contracts\Modifier;
use Illuminate\Support\Str;

class Password implements Modifier
{
    public function title(): string
    {
        return 'Password hash value';
    }

    public function description(): string
    {
        return 'Treat the the value as a password and securely hash it using the chosen hashing algorithm.<br>
            Your default hashing algorithm is: <b>'. Hash::getDefaultDriver() . '</b>';
    }

    public function settings(): array
    {
        return [
            'algorithm' => [
                'type' => 'select',
                'options' => [
                    'bcrypt',
                    'argon',
                    'argon2id'
                ],
                'help' => 'See the <a href="https://laravel.com/docs/hashing" target="_blank">Laravel documentation</a>
                    for more info on password hashing',
            ],
        ];
    }

    public function handle($value = null, array $settings = []): string
    {
        return Hash::driver($settings['algorithm'])->make($value);
    }
}
