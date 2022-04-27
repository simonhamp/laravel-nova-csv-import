<?php

namespace SimonHamp\LaravelNovaCsvImport\Modifiers;

use SimonHamp\LaravelNovaCsvImport\Contracts\Modifier;

class Boolean implements Modifier
{
    public function title(): string
    {
        return 'Boolean';
    }

    public function description(): string
    {
        return "Converts the value to a strict boolean. The following values are considered `false`:
            false, 'false', 0, '0', '', 'off', 'no', null
            Everything else is considered `true`";
    }

    public function settings(): array
    {
        return [];
    }

    public function handle($value = null, array $settings = []): bool
    {
        switch (strtolower($value)) {
            case false:
            case 'false':
            case 0:
            case '0':
            case '':
            case 'off':
            case 'no':
            case null:
                return false;
                break;
            default:
                return true;
                break;
        }
    }
}
