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
        return "Converts the value to a strict boolean. The following values are considered <code>false</code>:
            <code>false</code>, <code>'false'</code>, <code>0</code>, <code>'0'</code>, <code>''</code>,
            <code>'off'</code>, <code>'no'</code>, <code>null</code>.<br>
            Everything else is considered <code>true</code>";
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
