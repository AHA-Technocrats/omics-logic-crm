<?php

namespace App\Firebase\Services;

use Carbon\Carbon;

class AchievementTimelineMapper
{
    /**
     * @param  array<int, array<string, mixed>>  $achievements
     * @return array<int, array<string, mixed>>
     */
    public function mapMany(array $achievements): array
    {
        $events = array_map(fn (array $achievement) => $this->map($achievement), $achievements);

        usort($events, function (array $a, array $b) {
            return strcmp((string) ($b['occurred_at'] ?? ''), (string) ($a['occurred_at'] ?? ''));
        });

        return array_values($events);
    }

    /**
     * @param  array<string, mixed>  $achievement
     * @return array<string, mixed>
     */
    public function map(array $achievement): array
    {
        $type = strtolower((string) ($achievement['type'] ?? $achievement['eventType'] ?? 'default'));
        $icons = (array) config('firebase.achievements.type_icons', []);
        $iconConfig = $icons[$type] ?? $icons['default'] ?? ['icon' => 'icon-activity', 'icon_class' => 'bg-gray-100 text-gray-800'];

        $title = $achievement['achievementTitle']
            ?? $achievement['title']
            ?? $achievement['name']
            ?? $this->buildLessonTitle($achievement);

        $detail = $achievement['platform']
            ?? $achievement['course']
            ?? $achievement['courseName']
            ?? $achievement['program']
            ?? $achievement['category']
            ?? $this->formatCourseSlug($achievement['courseSlug'] ?? null);

        $occurredAt = $this->resolveTimestamp($achievement);

        return [
            'id' => $achievement['id'] ?? null,
            'icon' => $iconConfig['icon'],
            'icon_class' => $iconConfig['icon_class'],
            'title' => $title,
            'detail' => $detail,
            'quote' => $achievement['feedback'] ?? $achievement['comment'] ?? $achievement['review'] ?? null,
            'rating' => isset($achievement['rating']) ? (int) $achievement['rating'] : null,
            'occurred_at' => $occurredAt?->toIso8601String(),
            'relative' => $occurredAt?->diffForHumans(),
            'absolute' => $occurredAt?->format('M Y'),
        ];
    }

    /**
     * @param  array<string, mixed>  $achievement
     */
    protected function buildLessonTitle(array $achievement): string
    {
        $lesson = $achievement['lesson'] ?? $achievement['lessonName'] ?? $achievement['lessonTitle'] ?? null;

        if ($lesson) {
            return 'Completed '.$lesson;
        }

        return (string) ($achievement['description'] ?? 'Portal activity');
    }

    protected function formatCourseSlug(?string $slug): ?string
    {
        if (! $slug) {
            return null;
        }

        return ucwords(str_replace(['-', '_'], ' ', $slug));
    }

    /**
     * @param  array<string, mixed>  $achievement
     */
    protected function resolveTimestamp(array $achievement): ?Carbon
    {
        foreach ((array) config('firebase.achievements.timestamp_fields', []) as $field) {
            if (! empty($achievement[$field])) {
                try {
                    return Carbon::parse($achievement[$field]);
                } catch (\Throwable) {
                    continue;
                }
            }
        }

        return null;
    }
}
