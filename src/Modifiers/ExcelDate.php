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
        return 'Interprets the given value as an Excel date-time float and converts it to a DateTime object
            and formatted according to the supplied <code>format</code> setting';
    }

    public function settings(): array
    {
        // TODO: Change to a list of common formats and support custom formats
        return [
            'format' => [
                'type' => 'string',
                'default' => 'Y-m-d H:i:s',
                'help' => 'See the PHP documentation on
                    <a href="https://www.php.net/manual/en/datetime.formats.php" target="_blank">
                        Supported Date and Time Formats
                    </a>',
            ],
        ];
    }

    public function handle($value = null, array $settings = []): string
    {
        return Date::excelToDateTimeObject($value)
            ->format($settings['format'] ?? $this->settings()['format']['default']);
    }
}
