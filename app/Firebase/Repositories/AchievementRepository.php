<?php

namespace App\Firebase\Repositories;

class AchievementRepository extends BaseFirestoreRepository
{
    /**
     * @return array{items: array<int, array<string, mixed>>, meta: array<string, mixed>}
     */
    public function getUserAchievements(string $uid, int $limit = 20, ?string $cursor = null): array
    {
        return $this->runQuery(function () use ($uid, $limit, $cursor) {
            $parent = sprintf(
                'projects/%s/databases/(default)/documents/%s/%s',
                $this->firebase->firestore()->projectId,
                $this->usersCollection(),
                $uid,
            );

            $startAfter = $this->decodeCursor($cursor)['id'] ?? null;

            $orderField = (string) config('firebase.achievements.order_field', 'timeStamp');
            $orderDirection = strtoupper((string) config('firebase.achievements.order_direction', 'desc'));

            $items = $this->firebase->firestore()->queryCollection(
                $this->achievementsCollection(),
                parentDocumentPath: $parent,
                orderByField: $orderField,
                orderDirection: $orderDirection,
                limit: $limit + 1,
                startAfterDocumentId: $startAfter,
            );

            return $this->paginatedResult($items, $limit);
        });
    }
}
