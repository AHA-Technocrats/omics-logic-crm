<?php

namespace AHATechnocrats\WebForm\Helpers;

use AHATechnocrats\Product\Models\Product;
use AHATechnocrats\WebForm\Contracts\WebForm as WebFormContract;
use Illuminate\Support\Collection;

class WebFormCampaigns
{
    /**
     * @return Collection<int, Product>
     */
    public static function active(): Collection
    {
        return Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * @return list<array{id: int, key: string, name: string}>
     */
    public static function activeAsOptions(): array
    {
        return self::active()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'key' => (string) $product->id,
                'name' => $product->name,
            ])
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, Product>
     */
    public static function forForm(WebFormContract $webForm): Collection
    {
        $query = Product::query()
            ->where('is_active', true)
            ->orderBy('name');

        if (self::usesSelectedScope($webForm)) {
            $ids = self::selectedIds($webForm);

            if ($ids !== []) {
                $query->whereIn('id', $ids);
            }
        }

        return $query->get();
    }

    public static function usesSelectedScope(WebFormContract $webForm): bool
    {
        return ($webForm->campaign_scope ?? 'all') === 'selected';
    }

    /**
     * @return list<int>
     */
    public static function selectedIds(WebFormContract $webForm): array
    {
        $options = $webForm->campaign_options ?? $webForm->program_options ?? null;

        if (is_string($options)) {
            $options = json_decode($options, true);
        }

        if (! is_array($options) || $options === []) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map(
            fn ($id) => is_numeric($id) ? (int) $id : null,
            $options
        ))));
    }

    /**
     * @return list<int>|null
     */
    public static function normalizeOptionsInput(mixed $input, string $scope = 'all'): ?array
    {
        if ($scope !== 'selected') {
            return null;
        }

        if ($input === null || $input === '') {
            return null;
        }

        if (is_string($input)) {
            $input = json_decode($input, true);
        }

        if (! is_array($input) || $input === []) {
            return null;
        }

        $validIds = Product::query()
            ->where('is_active', true)
            ->whereIn('id', array_map('intval', $input))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return $validIds === [] ? null : array_values($validIds);
    }
}
