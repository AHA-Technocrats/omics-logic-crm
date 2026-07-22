<?php

namespace App\Firebase\Repositories;

class UserRepository extends BaseFirestoreRepository
{
    /**
     * @return array<string, mixed>|null
     */
    public function findByEmail(string $email): ?array
    {
        return $this->runQuery(function () use ($email) {
            $results = $this->firebase->firestore()->queryCollection(
                $this->usersCollection(),
                fieldFilter: [
                    'field' => ['fieldPath' => 'email'],
                    'op' => 'EQUAL',
                    'value' => ['stringValue' => $email],
                ],
                limit: 1,
            );

            if (empty($results)) {
                return null;
            }

            $user = $results[0];
            $user['uid'] = $user['id'] ?? null;

            return $user;
        });
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getUserByUid(string $uid): ?array
    {
        return $this->runQuery(function () use ($uid) {
            $user = $this->firebase->firestore()->getDocument($this->usersCollection(), $uid);

            if ($user === null) {
                return null;
            }

            $user['uid'] = $uid;

            return $user;
        });
    }

    /**
     * @return array{items: array<int, array<string, mixed>>, meta: array<string, mixed>}
     */
    public function getUsers(int $limit = 100, ?string $cursor = null): array
    {
        return $this->runQuery(function () use ($limit, $cursor) {
            $startAfter = $this->decodeCursor($cursor)['id'] ?? null;

            $items = $this->firebase->firestore()->queryCollection(
                $this->usersCollection(),
                limit: $limit + 1,
                startAfterDocumentId: $startAfter,
            );

            // Ensure uid is populated for each user
            foreach ($items as &$item) {
                $item['uid'] = $item['id'] ?? null;
            }

            return $this->paginatedResult($items, $limit);
        });
    }
}
