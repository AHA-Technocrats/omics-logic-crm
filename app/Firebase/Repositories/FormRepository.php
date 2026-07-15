<?php

namespace App\Firebase\Repositories;

use Carbon\Carbon;

class FormRepository extends BaseFirestoreRepository
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function getForms(): array
    {
        return $this->getPaginatedForms(
            (int) config('firebase.pagination.max_limit', 100)
        )['items'];
    }

    /**
     * @return array{items: array<int, array<string, mixed>>, meta: array<string, mixed>}
     */
    public function getPaginatedForms(
        int $limit = 20,
        ?string $cursor = null,
        ?string $sortField = null,
        string $sortDirection = 'desc',
        ?Carbon $from = null,
        ?Carbon $to = null,
    ): array {
        return $this->runQuery(function () use ($limit, $cursor, $sortField, $sortDirection, $from, $to) {
            $dateField = $sortField ?: (string) config('firebase.forms.default_sort_field', 'submittedAt');
            $startAfter = $this->decodeCursor($cursor)['id'] ?? null;

            $fieldFilter = $this->buildDateRangeFilter($dateField, $from, $to);

            $items = $this->firebase->firestore()->queryCollection(
                $this->formsCollection(),
                fieldFilter: $fieldFilter,
                orderByField: $dateField,
                orderDirection: $sortDirection,
                limit: $limit + 1,
                startAfterDocumentId: $startAfter,
            );

            return $this->paginatedResult($items, $limit);
        });
    }

    /**
     * @return array{items: array<int, array<string, mixed>>, meta: array<string, mixed>}
     */
    public function getFormsByDateRange(?Carbon $from, ?Carbon $to, int $limit = 20, ?string $cursor = null): array
    {
        return $this->getPaginatedForms($limit, $cursor, null, 'desc', $from, $to);
    }

    /**
     * @return array{items: array<int, array<string, mixed>>, meta: array<string, mixed>}
     */
    public function getFormsSince(?Carbon $since, int $limit = 50, ?string $cursor = null): array
    {
        $dateField = (string) config('firebase.forms.date_field', 'submittedAt');

        if ($since === null) {
            return $this->getPaginatedForms($limit, $cursor, $dateField, 'desc');
        }

        return $this->getPaginatedForms($limit, $cursor, $dateField, 'asc', $since, null);
    }

    protected function buildDateRangeFilter(string $dateField, ?Carbon $from, ?Carbon $to): ?array
    {
        $formatDateStr = fn (Carbon $date) => $date->clone()->utc()->format('Y-m-d\TH:i:s.v\Z');
        $formatDateInt = fn (Carbon $date) => (string) $date->getTimestampMs();

        $buildConditions = function (string $op, Carbon $date) use ($dateField, $formatDateStr, $formatDateInt) {
            return [
                'compositeFilter' => [
                    'op' => 'OR',
                    'filters' => [
                        [
                            'fieldFilter' => [
                                'field' => ['fieldPath' => $dateField],
                                'op' => $op,
                                'value' => ['integerValue' => $formatDateInt($date)],
                            ],
                        ],
                        [
                            'fieldFilter' => [
                                'field' => ['fieldPath' => $dateField],
                                'op' => $op,
                                'value' => ['stringValue' => $formatDateStr($date)],
                            ],
                        ],
                        [
                            'fieldFilter' => [
                                'field' => ['fieldPath' => $dateField],
                                'op' => $op,
                                'value' => ['timestampValue' => $formatDateStr($date)],
                            ],
                        ],
                    ],
                ],
            ];
        };

        if ($from && $to) {
            return [
                'compositeFilter' => [
                    'op' => 'AND',
                    'filters' => [
                        $buildConditions('GREATER_THAN_OR_EQUAL', $from),
                        $buildConditions('LESS_THAN_OR_EQUAL', $to),
                    ],
                ],
            ];
        }

        if ($from) {
            return $buildConditions('GREATER_THAN', $from);
        }

        if ($to) {
            return $buildConditions('LESS_THAN_OR_EQUAL', $to);
        }

        return null;
    }
}
