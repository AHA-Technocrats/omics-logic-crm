<?php

namespace AHATechnocrats\WebForm\Helpers;

use AHATechnocrats\WebForm\Contracts\WebForm as WebFormContract;

class WebFormPrograms
{
    /**
     * @return list<array{key: string, name: string, id?: int}>
     */
    public static function all(): array
    {
        return WebFormCampaigns::activeAsOptions();
    }

    /**
     * @return list<string>
     */
    public static function allKeys(): array
    {
        return array_column(self::all(), 'key');
    }

    public static function isEnabled(WebFormContract $webForm): bool
    {
        return ($webForm->program_field ?? 'required') !== 'none';
    }

    /**
     * @return list<array{key: string, name: string, id?: int}>
     */
    public static function forForm(WebFormContract $webForm): array
    {
        return WebFormCampaigns::forForm($webForm)
            ->map(fn ($product) => [
                'id' => $product->id,
                'key' => (string) $product->id,
                'name' => $product->name,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<string>|null
     */
    public static function normalizeOptionsInput(mixed $input): ?array
    {
        if ($input === null || $input === '') {
            return null;
        }

        if (is_string($input)) {
            $input = json_decode($input, true);
        }

        if (! is_array($input) || $input === []) {
            return null;
        }

        $validKeys = self::allKeys();

        $normalized = array_values(array_unique(array_filter(
            array_map(
                fn ($key) => is_scalar($key) ? (string) $key : null,
                $input
            ),
            fn ($key) => $key !== null && in_array($key, $validKeys, true)
        )));

        return $normalized === [] ? null : $normalized;
    }
}
