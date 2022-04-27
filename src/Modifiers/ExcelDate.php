<?php

namespace SimonHamp\LaravelNovaCsvImport\Modifiers;

use PhpOffice\PhpSpreadsheet\Shared\Date;
use SimonHamp\LaravelNovaCsvImport\Contracts\Modifier;

class ExcelDate implements Modifier
{
    public function title(): string
    {
        return 'Excel Date Parser';
    }

    public function description(): string
    {
        return "Interprets the given value as an Excel date-time float and converts it to a DateTime object
            and formatted according to the supplied `format` setting";
    }

    public function settings(): array
    {
        return [
            'format' => [
                'type' => 'string',
                'default' => 'Y-m-d H:i:s',
            ]
        ];
    }

    public function handle($value = null, array $settings = []): string
    {
        return Date::excelToDateTimeObject($value)
            ->format($settings['format'] ?? $this->settings()['format']['default']);
    }
}
