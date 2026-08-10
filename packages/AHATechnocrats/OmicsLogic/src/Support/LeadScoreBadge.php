<?php

namespace AHATechnocrats\OmicsLogic\Support;

class LeadScoreBadge
{
    public const PRODUCT_INTEREST_MAX = 35;

    public const EMAIL_DOMAIN_MAX = 25;

    public const COUNTRY_MAX = 20;

    public const PROFILE_MAX = 20;

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

    /**
     * @return array{
     *     total: int,
     *     band: string,
     *     label: string,
     *     class: string,
     *     factors: list<array{key: string, label: string, points: int, max: int}>
     * }
     */
    public static function breakdown(
        int $score,
        ?string $band,
        int $productInterest,
        int $emailDomain,
        int $country,
        int $profile,
    ): array {
        $meta = self::meta($score, $band);

        return [
            'total' => $score,
            'band' => $meta['band'],
            'label' => $meta['label'],
            'class' => $meta['class'],
            'factors' => [
                [
                    'key' => 'product_interest',
                    'label' => trans('omicslogic::app.fields.score-tip.product-interest'),
                    'points' => $productInterest,
                    'max' => self::PRODUCT_INTEREST_MAX,
                ],
                [
                    'key' => 'email_domain',
                    'label' => trans('omicslogic::app.fields.score-tip.email-domain'),
                    'points' => $emailDomain,
                    'max' => self::EMAIL_DOMAIN_MAX,
                ],
                [
                    'key' => 'country',
                    'label' => trans('omicslogic::app.fields.score-tip.country'),
                    'points' => $country,
                    'max' => self::COUNTRY_MAX,
                ],
                [
                    'key' => 'profile',
                    'label' => trans('omicslogic::app.fields.score-tip.profile'),
                    'points' => $profile,
                    'max' => self::PROFILE_MAX,
                ],
            ],
        ];
    }

    /**
     * @return array{
     *     total: int,
     *     band: string,
     *     label: string,
     *     class: string,
     *     factors: list<array{key: string, label: string, points: int, max: int}>
     * }
     */
    public static function breakdownFromPerson(object $person): array
    {
        $score = (int) ($person->lead_score ?? 0);

        return self::breakdown(
            $score,
            $person->score_band ?? null,
            (int) ($person->product_interest_points ?? 0),
            (int) ($person->email_domain_points ?? 0),
            (int) ($person->country_points ?? 0),
            (int) ($person->profile_points ?? 0),
        );
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
