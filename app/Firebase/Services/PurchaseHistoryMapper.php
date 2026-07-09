<?php

namespace App\Firebase\Services;

use Carbon\Carbon;

class PurchaseHistoryMapper
{
    /**
     * @param  array<int, array<string, mixed>>  $purchases
     * @return array<int, array<string, mixed>>
     */
    public function mapMany(array $purchases): array
    {
        $items = array_map(fn (array $purchase) => $this->map($purchase), $purchases);

        return $this->sortItems($items);
    }

    /**
     * @param  array<int, array<string, mixed>>  $achievements
     * @return array<int, array<string, mixed>>
     */
    public function mapManyFromAchievements(array $achievements): array
    {
        $purchaseAchievements = $this->filterPurchaseAchievements($achievements);
        $groupedAchievements = $this->groupPurchaseAchievements($purchaseAchievements);

        return $this->mapMany($groupedAchievements);
    }

    /**
     * @param  array<string, mixed>  $purchase
     * @return array<string, mixed>
     */
    public function map(array $purchase): array
    {
        $title = $this->resolveTitle($purchase);
        $amount = $this->firstNumeric($purchase, (array) config('firebase.purchases.amount_fields', []));
        $currency = $this->firstString($purchase, (array) config('firebase.purchases.currency_fields', []))
            ?? (string) config('app.currency', 'USD');

        $status = strtolower($this->firstString($purchase, (array) config('firebase.purchases.status_fields', [])) ?? 'completed');
        $occurredAt = $this->resolveTimestamp($purchase);

        return [
            'id' => $purchase['id'] ?? null,
            'title' => $title,
            'detail' => $this->resolveDetail($purchase),
            'amount' => $amount,
            'currency' => $currency,
            'amount_label' => $this->formatAmount($amount, $currency),
            'status' => $status,
            'order_id' => $this->firstString($purchase, ['orderId', 'order_id', 'transactionId', 'transaction_id', 'paymentId', 'payment_id']),
            'occurred_at' => $occurredAt?->toIso8601String(),
            'relative' => $occurredAt?->diffForHumans(),
            'absolute' => $occurredAt?->format('M j, Y'),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $achievements
     * @return array<int, array<string, mixed>>
     */
    protected function filterPurchaseAchievements(array $achievements): array
    {
        return array_values(array_filter(
            $achievements,
            fn (array $achievement) => $this->isPurchaseAchievement($achievement),
        ));
    }

    /**
     * @param  array<int, array<string, mixed>>  $achievements
     * @return array<int, array<string, mixed>>
     */
    protected function groupPurchaseAchievements(array $achievements): array
    {
        $grouped = [];

        foreach ($achievements as $achievement) {
            $key = $achievement['lessonId']
                ?? $achievement['courseId']
                ?? $achievement['courseSlug']
                ?? $achievement['id']
                ?? spl_object_hash((object) $achievement);

            if (! isset($grouped[$key]) || $this->isNewerAchievement($achievement, $grouped[$key])) {
                $grouped[$key] = $achievement;
            }
        }

        return array_values($grouped);
    }

    /**
     * @param  array<string, mixed>  $achievement
     */
    protected function isPurchaseAchievement(array $achievement): bool
    {
        $type = strtolower((string) ($achievement['type'] ?? $achievement['eventType'] ?? ''));
        $purchaseTypes = array_map(
            'strtolower',
            (array) config('firebase.purchases.achievement_types', ['purchase', 'course']),
        );

        if (in_array($type, $purchaseTypes, true)) {
            return true;
        }

        $title = (string) ($achievement['achievementTitle'] ?? $achievement['title'] ?? '');

        foreach ((array) config('firebase.purchases.achievement_title_patterns', []) as $pattern) {
            if (@preg_match($pattern, $title) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $current
     * @param  array<string, mixed>  $existing
     */
    protected function isNewerAchievement(array $current, array $existing): bool
    {
        $currentAt = $this->resolveTimestamp($current);
        $existingAt = $this->resolveTimestamp($existing);

        if ($currentAt && $existingAt) {
            return $currentAt->greaterThan($existingAt);
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $purchase
     */
    protected function resolveTitle(array $purchase): string
    {
        $rawTitle = $this->firstString($purchase, (array) config('firebase.purchases.title_fields', []));

        if ($rawTitle) {
            $extracted = $this->extractCourseNameFromTitle($rawTitle);

            if ($extracted) {
                return $extracted;
            }

            if (! $this->isGenericAchievementTitle($rawTitle)) {
                return $rawTitle;
            }
        }

        return $this->formatSlug($purchase['courseSlug'] ?? $purchase['productSlug'] ?? null)
            ?? 'Course enrollment';
    }

    /**
     * @param  array<string, mixed>  $purchase
     */
    protected function resolveDetail(array $purchase): ?string
    {
        $platform = $this->firstString($purchase, ['platform', 'paymentMethod', 'payment_method', 'category', 'program']);

        if ($platform) {
            return $platform;
        }

        $parentCourse = $this->formatSlug($purchase['courseSlug'] ?? null);
        $lesson = $this->formatSlug($purchase['lessonSlug'] ?? null);

        if ($parentCourse && $lesson && $parentCourse !== $lesson) {
            return $parentCourse;
        }

        return $parentCourse;
    }

    protected function extractCourseNameFromTitle(string $title): ?string
    {
        if (preg_match('/^Completed\s*\(([^)]+)\)/i', $title, $matches) === 1) {
            $name = trim($matches[1]);

            return $name !== '' ? $name : null;
        }

        if (preg_match('/^(?:Enrolled in|Purchased)\s+(.+)$/i', $title, $matches) === 1) {
            return trim($matches[1]) ?: null;
        }

        return null;
    }

    protected function isGenericAchievementTitle(string $title): bool
    {
        return preg_match('/^(?:Completion|Completed)\s*\(\s*\)/i', $title) === 1;
    }

    /**
     * @param  array<string, mixed>  $document
     */
    protected function firstString(array $document, array $fields): ?string
    {
        foreach ($fields as $field) {
            if (! empty($document[$field]) && is_scalar($document[$field])) {
                return (string) $document[$field];
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $document
     */
    protected function firstNumeric(array $document, array $fields): ?float
    {
        foreach ($fields as $field) {
            if (isset($document[$field]) && is_numeric($document[$field])) {
                return (float) $document[$field];
            }
        }

        return null;
    }

    protected function formatSlug(?string $slug): ?string
    {
        if (! $slug) {
            return null;
        }

        return ucwords(str_replace(['-', '_'], ' ', $slug));
    }

    protected function formatAmount(?float $amount, string $currency): ?string
    {
        if ($amount === null) {
            return null;
        }

        return sprintf('%s %.2f', strtoupper($currency), $amount);
    }

    /**
     * @param  array<string, mixed>  $document
     */
    protected function resolveTimestamp(array $document): ?Carbon
    {
        $fields = array_unique(array_merge(
            (array) config('firebase.purchases.timestamp_fields', []),
            (array) config('firebase.achievements.timestamp_fields', []),
        ));

        foreach ($fields as $field) {
            if (! empty($document[$field])) {
                try {
                    return Carbon::parse($document[$field]);
                } catch (\Throwable) {
                    continue;
                }
            }
        }

        return null;
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    protected function sortItems(array $items): array
    {
        usort($items, function (array $a, array $b) {
            return strcmp((string) ($b['occurred_at'] ?? ''), (string) ($a['occurred_at'] ?? ''));
        });

        return array_values($items);
    }
}
