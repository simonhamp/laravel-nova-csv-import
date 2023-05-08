<?php

namespace SimonHamp\LaravelNovaCsvImport\Modifiers;

use Exception;
use Illuminate\Support\Facades\Hash as LaravelHashFascade;
use SimonHamp\LaravelNovaCsvImport\Contracts\Modifier;
use Illuminate\Support\Str;

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
            'algorithm' => array_merge(
                [
                    'laravel.password',
                    'laravel.bcrypt',
                    'laravel.argon',
                    'laravel.argon2id'
                ],
                hash_algos()
            ),
        ];
    }

    public function handle($value = null, array $settings = []): string
    {
        if (Str::startsWith($settings['algorithm'], 'laravel.')) {
            return $this->handleLaravelHash($value, $settings);
        }

        return hash($settings['algorithm'], $value);
    }

    public function handleLaravelHash($value = null, array $settings = []): string
    {
        switch ($settings['algorithm']) {
            case 'laravel.password':
                return LaravelHashFascade::make($value);
                break;
            case 'laravel.bcrypt':
                return LaravelHashFascade::driver('bcrypt')->make($value);
                break;
            case 'laravel.argon':
                return LaravelHashFascade::driver('argon')->make($value);
                break;
            case 'laravel.argon2id':
                return LaravelHashFascade::driver('argon2id')->make($value);
                break;
        }
    }
}
