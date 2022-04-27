<?php

namespace SimonHamp\LaravelNovaCsvImport\Contracts;

interface Modifier
{
    public function title(): string;

    public function description(): string;

    public function settings(): array;

    public function handle($value = null, array $settings = []);
}
