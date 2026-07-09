<?php

namespace App\Firebase\Repositories;

use App\Firebase\Exceptions\FirebaseConnectionException;
use App\Firebase\FirebaseManager;

abstract class BaseFirestoreRepository
{
    public function __construct(protected FirebaseManager $firebase) {}

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array{items: array<int, array<string, mixed>>, meta: array<string, mixed>}
     */
    protected function paginatedResult(array $items, int $limit, ?string $nextCursor = null): array
    {
        $hasMore = count($items) > $limit;

        if ($hasMore) {
            array_pop($items);
        }

        $next = $hasMore && ! empty($items)
            ? $this->encodeCursor($items[array_key_last($items)])
            : $nextCursor;

        return [
            'items' => $items,
            'meta' => [
                'limit' => $limit,
                'has_more' => $hasMore,
                'next_cursor' => $next,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $document
     */
    protected function encodeCursor(array $document): string
    {
        return base64_encode(json_encode([
            'id' => $document['id'] ?? null,
        ]) ?: '');
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function decodeCursor(?string $cursor): ?array
    {
        if (! $cursor) {
            return null;
        }

        $decoded = json_decode(base64_decode($cursor) ?: '', true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    protected function runQuery(callable $callback): mixed
    {
        try {
            return $callback();
        } catch (FirebaseConnectionException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            throw new FirebaseConnectionException('Unable to query Firestore.');
        }
    }

    protected function usersCollection(): string
    {
        return (string) config('firebase.collections.users', 'Users');
    }

    protected function achievementsCollection(): string
    {
        return (string) config('firebase.collections.achievements', 'Achievements');
    }

    protected function formsCollection(): string
    {
        return (string) config('firebase.collections.forms', 'Forms');
    }

    protected function purchasesCollection(): string
    {
        return (string) config('firebase.collections.purchases', 'Purchases');
    }
}
