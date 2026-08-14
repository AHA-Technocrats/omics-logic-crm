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

            // Selected scope with no valid IDs must not fall back to "all campaigns".
            if ($ids === []) {
                return collect();
            }

            $query->whereIn('id', $ids);
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
     * Keep "selected campaigns only" forms in sync when a new/active campaign is added.
     */
    public static function appendToSelectedForms(Product $product): void
    {
        if (! $product->is_active) {
            return;
        }

        $forms = \AHATechnocrats\WebForm\Models\WebForm::query()
            ->where('campaign_scope', 'selected')
            ->get();

        foreach ($forms as $form) {
            $ids = self::selectedIds($form);

            if (in_array((int) $product->id, $ids, true)) {
                continue;
            }

            $ids[] = (int) $product->id;
            $form->program_options = $ids;
            $form->save();
        }
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
