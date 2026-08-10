<?php

namespace AHATechnocrats\Product\Repositories;

use AHATechnocrats\Core\Eloquent\Repository;
use AHATechnocrats\Product\Models\ProductProxy;
use Illuminate\Support\Facades\DB;

class CampaignCategoryRepository extends Repository
{
    /**
     * @return mixed
     */
    public function model()
    {
        return 'AHATechnocrats\Product\Contracts\CampaignCategory';
    }

    public function findByNameInsensitive(string $name)
    {
        return $this->getModel()
            ->newQuery()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($name))])
            ->first();
    }

    public function nameExists(string $name, ?int $exceptId = null): bool
    {
        $query = $this->getModel()
            ->newQuery()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($name))]);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }

    /**
     * Rename and cascade the denormalized category name on products.
     */
    public function rename(int $id, string $name)
    {
        return DB::transaction(function () use ($id, $name) {
            $category = $this->findOrFail($id);
            $previous = $category->name;

            $category = $this->update(['name' => trim($name)], $id);

            if ($previous !== $category->name) {
                ProductProxy::modelClass()::query()
                    ->where('category_id', $id)
                    ->update(['category' => $category->name]);
            }

            return $category;
        });
    }

    /**
     * Find an existing category by name or create one.
     */
    public function findOrCreateByName(string $name)
    {
        $name = trim($name);

        if ($name === '') {
            return null;
        }

        $existing = $this->findByNameInsensitive($name);

        if ($existing) {
            return $existing;
        }

        return $this->create(['name' => $name]);
    }
}
