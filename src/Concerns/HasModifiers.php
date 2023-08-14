<?php

namespace SimonHamp\LaravelNovaCsvImport\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use SimonHamp\LaravelNovaCsvImport\Contracts\Modifier;
use SimonHamp\LaravelNovaCsvImport\Modifiers\Boolean;
use SimonHamp\LaravelNovaCsvImport\Modifiers\DefaultValue;
use SimonHamp\LaravelNovaCsvImport\Modifiers\ExcelDate;
use SimonHamp\LaravelNovaCsvImport\Modifiers\Hash;
use SimonHamp\LaravelNovaCsvImport\Modifiers\Prefix;
use SimonHamp\LaravelNovaCsvImport\Modifiers\Str as StrModifier;
use SimonHamp\LaravelNovaCsvImport\Modifiers\Suffix;

trait HasModifiers
{
    protected $modifiers = [];

    protected static $registered_modifiers = [];

    protected function bootHasModifiers()
    {
        // Register built-in modifiers
        static::registerModifiers(
            new Boolean,
            new DefaultValue,
            new ExcelDate,
            new StrModifier,
            new Hash,
            new Prefix,
            new Suffix,
        );
    }

    public static function registerModifiers(Modifier ...$modifiers)
    {
        foreach ($modifiers as $modifier) {
            $name = Str::snake(class_basename($modifier));

            static::$registered_modifiers[$name] = $modifier;
        }
    }

    public function getAvailableModifiers()
    {
        return collect(static::$registered_modifiers)
            ->map(function (Modifier $modifier, $key) {
                return [
                    'name' => $key,
                    'title' => $modifier->title(),
                    'description' => $modifier->description(),
                    'settings' => $this->formatModifierSettings($modifier->settings()),
                ];
            })
            ->keyBy('name');
    }

    public function getModifiers($key = null): array
    {
        if ($key) {
            return $this->modifiers[$key] ?? [];
        }

        return $this->modifiers;
    }

    public function setModifiers(array $map): self
    {
        $this->modifiers = $map;

        return $this;
    }

    protected function modifyValue($value = null, array $modifiers = [])
    {
        foreach ($modifiers as $modifier) {
            $instance = static::$registered_modifiers[$modifier['name']];

            $value = $instance->handle($value, $modifier['settings'] ?? []);
        }

        return $value;
    }

    protected function formatModifierSettings(array $settings = [])
    {
        $normalised_settings = [];

        foreach ($settings as $name => $setting) {
            $normalised = [
                'title' => $setting['title'] ?? Str::title($name),
                'type' => is_string($setting) ? 'string' : $setting['type'] ?? 'select',
                'default' => $setting['default'] ?? '',
                'help' => $setting['help'] ?? '',
            ];

            // Make <select>s always have key-value pairs
            if ($normalised['type'] === 'select') {
                $options = $setting['options'] ?? $setting;

                $normalised['options'] = Arr::isAssoc($options) ? $options : array_combine($options, $options);
            }

            $normalised_settings[$name] = $normalised;
        }

        return $normalised_settings;
    }
}
