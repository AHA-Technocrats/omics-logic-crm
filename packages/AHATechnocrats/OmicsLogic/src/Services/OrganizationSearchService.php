<?php

namespace AHATechnocrats\OmicsLogic\Services;

use AHATechnocrats\Contact\Models\Organization;
use Illuminate\Support\Collection;

class OrganizationSearchService
{
    /**
     * @return Collection<int, Organization>
     */
    public function search(string $query, int $limit = 15): Collection
    {
        $query = trim($query);

        if (strlen($query) < 2) {
            return collect();
        }

        return Organization::query()
            ->where('name', 'like', '%'.$query.'%')
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name', 'type', 'country_code']);
    }
}
