<?php

namespace App\Firebase\Services;

use App\Firebase\Exceptions\FirebaseConnectionException;
use App\Firebase\Repositories\FormRepository;
use Carbon\Carbon;

class FormService
{
    public function __construct(protected FormRepository $formRepository) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getForms(array $filters = []): array
    {
        $limit = min(
            (int) ($filters['limit'] ?? config('firebase.pagination.default_limit', 20)),
            (int) config('firebase.pagination.max_limit', 100),
        );

        $cursor = $filters['cursor'] ?? null;
        $sort = $filters['sort'] ?? null;
        $sortField = null;
        $sortDirection = (string) config('firebase.forms.default_sort_direction', 'desc');

        if (is_string($sort) && str_contains($sort, ':')) {
            [$sortField, $sortDirection] = explode(':', $sort, 2);
        }

        $from = ! empty($filters['from']) ? Carbon::parse($filters['from']) : null;
        $to = ! empty($filters['to']) ? Carbon::parse($filters['to']) : null;

        try {
            $result = ($from || $to)
                ? $this->formRepository->getFormsByDateRange($from, $to, $limit, $cursor)
                : $this->formRepository->getPaginatedForms($limit, $cursor, $sortField, $sortDirection);
        } catch (FirebaseConnectionException) {
            return [
                'success' => false,
                'message' => 'Unable to fetch data at this time.',
                'status' => 503,
            ];
        }

        return [
            'success' => true,
            'message' => 'Forms fetched successfully.',
            'data' => $result['items'],
            'meta' => $result['meta'],
        ];
    }

    /**
     * @return array{items: array<int, array<string, mixed>>, meta: array<string, mixed>}|array<string, mixed>
     */
    public function getFormsSince(?Carbon $since, int $limit = 50, ?string $cursor = null): array
    {
        try {
            return $this->formRepository->getFormsSince($since, $limit, $cursor);
        } catch (FirebaseConnectionException) {
            return [
                'success' => false,
                'message' => 'Unable to fetch data at this time.',
                'status' => 503,
            ];
        }
    }
}
