<?php

namespace AHATechnocrats\OmicsLogic\Support;

class LeadScoreBadge
{
    /**
     * @return array{band: string, label: string, class: string}
     */
    public static function meta(int $score, ?string $band = null): array
    {
        $band = $band ?: self::bandFromScore($score);

        return [
            'band' => $band,
            'label' => ucfirst($band),
            'class' => match ($band) {
                'hot' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                'warm' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
                'nurture' => 'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-300',
                default => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300',
            },
        ];
    }

    public static function html(int $score, ?string $band = null): string
    {
        $meta = self::meta($score, $band);

        return '<span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold '.$meta['class'].'">'
            .e((string) $score)
            .' · '
            .e($meta['label'])
            .'</span>';
    }

    public static function bandFromScore(int $score): string
    {
        $hotMin = (int) (function_exists('core') ? (core()->getConfigData('lead_score.bands.thresholds.hot_min') ?: 75) : 75);
        $warmMin = (int) (function_exists('core') ? (core()->getConfigData('lead_score.bands.thresholds.warm_min') ?: 55) : 55);
        $nurtureMin = (int) (function_exists('core') ? (core()->getConfigData('lead_score.bands.thresholds.nurture_min') ?: 35) : 35);

        $hotMin = $hotMin ?: 75;
        $warmMin = $warmMin ?: 55;
        $nurtureMin = $nurtureMin ?: 35;

        return match (true) {
            $score >= $hotMin => 'hot',
            $score >= $warmMin => 'warm',
            $score >= $nurtureMin => 'nurture',
            default => 'low',
        };
    }
}
