<?php

namespace AHATechnocrats\OmicsLogic\Services;

use AHATechnocrats\Contact\Models\Organization;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

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

        $dbOrganizations = Organization::query()
            ->where('name', 'like', '%'.$query.'%')
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name', 'type', 'country_code'])
            ->map(function ($org) {
                return [
                    'id' => $org->id,
                    'name' => $org->name,
                    'type' => $org->type,
                    'country_code' => $org->country_code,
                ];
            });

        $results = collect($dbOrganizations);

        if ($results->count() < $limit) {
            $jsonOrganizations = $this->searchJson($query, $limit - $results->count());

            foreach ($jsonOrganizations as $jsonOrg) {
                if (! $results->contains('name', $jsonOrg['name'])) {
                    $results->push([
                        'id' => $jsonOrg['id'],
                        'name' => $jsonOrg['name'],
                        'type' => \AHATechnocrats\OmicsLogic\Enums\OrganizationType::University->value,
                        'country_code' => $jsonOrg['country_code'],
                    ]);
                }
            }
        }

        return $results;
    }

    protected function searchJson(string $query, int $limit): array
    {
        $universities = Cache::remember('world_universities', 86400, function () {
            $path = storage_path('app/world_universities_and_domains.json');

            if (! file_exists($path)) {
                return [];
            }

            $content = file_get_contents($path);
            $data = json_decode($content, true) ?: [];

            $processed = [];
            foreach ($data as $item) {
                $processed[] = [
                    'id' => 'json|'.($item['alpha_two_code'] ?? ''),
                    'name' => $item['name'] ?? '',
                    'country_code' => $item['alpha_two_code'] ?? '',
                    'acronym' => $this->generateAcronym($item['name'] ?? ''),
                ];
            }

            return $processed;
        });

        $queryLower = strtolower($query);
        $matches = [];

        foreach ($universities as $uni) {
            $nameLower = strtolower($uni['name']);

            if (str_contains($nameLower, $queryLower) ||
                (strlen($queryLower) >= 2 && str_starts_with($uni['acronym'], $queryLower))) {

                $matches[] = $uni;

                if (count($matches) >= $limit) {
                    break;
                }
            }
        }

        return $matches;
    }

    protected function generateAcronym(string $name): string
    {
        $words = preg_split('/[\s\-]+/', $name);
        $acronym = '';
        $stopWords = ['of', 'and', 'the', 'for', 'in', 'at'];

        foreach ($words as $word) {
            if (empty($word)) {
                continue;
            }
            if (! in_array(strtolower($word), $stopWords)) {
                $acronym .= mb_substr($word, 0, 1);
            }
        }

        return strtolower($acronym);
    }
}
