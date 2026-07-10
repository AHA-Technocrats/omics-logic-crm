<?php

namespace AHATechnocrats\OmicsLogic\Support;

use Illuminate\Support\Str;

class LeadStageBadge
{
    public static function cell(?string $stageName, ?string $stageCode = null): string
    {
        if (! $stageName && ! $stageCode) {
            return '—';
        }

        $label = e(self::label($stageName, $stageCode));
        [$background, $color] = self::colors($stageName, $stageCode);

        return '<span style="display:inline-flex;align-items:center;background-color:'.$background.';color:'.$color.';border-radius:9999px;padding:4px 12px;font-size:12px;font-weight:600;">'
            .$label
            .'</span>';
    }

    protected static function label(?string $stageName, ?string $stageCode): string
    {
        $key = Str::lower(trim($stageCode ?: $stageName ?: ''));

        if (Str::contains($key, ['customer', 'won'])) {
            return 'Customer';
        }

        if (Str::contains($key, ['engaged', 'follow-up', 'follow up', 'prospect', 'negotiation'])) {
            return 'Engaged';
        }

        if (Str::contains($key, ['lead', 'new'])) {
            return 'Lead';
        }

        if (Str::contains($key, 'subscriber')) {
            return 'Subscriber';
        }

        if (Str::contains($key, ['dormant', 'lost'])) {
            return 'Dormant';
        }

        return $stageName ?: ucfirst((string) $stageCode);
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected static function colors(?string $stageName, ?string $stageCode): array
    {
        $key = Str::lower(trim($stageCode ?: $stageName ?: ''));

        if (Str::contains($key, ['customer', 'won'])) {
            return ['#dcfce7', '#15803d'];
        }

        if (Str::contains($key, ['engaged', 'follow-up', 'follow up', 'prospect', 'negotiation'])) {
            return ['#f3e8ff', '#7e22ce'];
        }

        if (Str::contains($key, ['lead', 'new'])) {
            return ['#dbeafe', '#1d4ed8'];
        }

        if (Str::contains($key, 'subscriber')) {
            return ['#f1f5f9', '#475569'];
        }

        if (Str::contains($key, ['dormant', 'lost'])) {
            return ['#f3f4f6', '#6b7280'];
        }

        return ['#f3f4f6', '#6b7280'];
    }
}
