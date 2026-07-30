<?php

namespace AHATechnocrats\OmicsLogic\Services;

use AHATechnocrats\Contact\Models\Person;
use Illuminate\Support\Str;

class LeadScoreCalculator
{
    /**
     * @return array{
     *     total: int,
     *     product_interest: int,
     *     email_domain: int,
     *     country: int,
     *     profile: int,
     *     band: string
     * }
     */
    public function evaluate(Person $person): array
    {
        $productInterest = $this->productInterestPoints($person);
        $emailDomain = $this->emailDomainPoints($person);
        $country = $this->countryPoints($person);
        $profile = $this->profilePoints($person);

        $total = (int) min(100, max(0, $productInterest + $emailDomain + $country + $profile));

        return [
            'total' => $total,
            'product_interest' => $productInterest,
            'email_domain' => $emailDomain,
            'country' => $country,
            'profile' => $profile,
            'band' => $this->bandFor($total),
        ];
    }

    public function calculate(Person $person): int
    {
        return $this->evaluate($person)['total'];
    }

    public function applyToPerson(Person $person): Person
    {
        $result = $this->evaluate($person);

        $person->lead_score = $result['total'];
        $person->product_interest_points = $result['product_interest'];
        $person->email_domain_points = $result['email_domain'];
        $person->country_points = $result['country'];
        $person->profile_points = $result['profile'];
        $person->score_band = $result['band'];

        return $person;
    }

    protected function productInterestPoints(Person $person): int
    {
        $scores = [];

        $person->loadMissing(['leads.products.product', 'primaryProduct']);

        foreach ($person->leads as $lead) {
            foreach ($lead->products as $leadProduct) {
                $campaign = $leadProduct->product;

                if ($campaign) {
                    $scores[] = $this->clampCampaignScore($campaign->product_interest_score ?? 5);
                }
            }
        }

        if ($person->primaryProduct) {
            $scores[] = $this->clampCampaignScore($person->primaryProduct->product_interest_score ?? 5);
        } elseif ($person->primary_product_id) {
            $scores[] = 5;
        }

        if ($scores === []) {
            return 5;
        }

        return max($scores);
    }

    protected function clampCampaignScore(mixed $score): int
    {
        return max(0, min(35, (int) $score));
    }

    protected function emailDomainPoints(Person $person): int
    {
        $emails = $this->personEmails($person);

        if ($emails === []) {
            return 5;
        }

        $institutional = $this->domainList('institutional');
        $company = $this->domainList('company');
        $personal = $this->domainList('personal');

        $best = 5;

        foreach ($emails as $email) {
            $domain = Str::lower(Str::after($email, '@'));

            if ($domain === '' || ! str_contains($email, '@')) {
                continue;
            }

            if ($this->domainMatches($domain, $institutional)) {
                return 25;
            }

            if ($this->domainMatches($domain, $company)) {
                $best = max($best, 15);
            } elseif ($this->domainMatches($domain, $personal)) {
                $best = max($best, 5);
            }
        }

        // Prefer institutional already returned; otherwise best among company/personal/unknown.
        foreach ($emails as $email) {
            $domain = Str::lower(Str::after($email, '@'));

            if ($this->domainMatches($domain, $institutional)) {
                return 25;
            }
        }

        return $best;
    }

    /**
     * @return list<string>
     */
    protected function personEmails(Person $person): array
    {
        $raw = $person->emails;

        if (! is_array($raw)) {
            return [];
        }

        $emails = [];

        foreach ($raw as $entry) {
            $value = is_array($entry) ? ($entry['value'] ?? null) : $entry;

            if (is_string($value) && trim($value) !== '') {
                $emails[] = Str::lower(trim($value));
            }
        }

        // Prefer institutional-looking emails first for matching order stability.
        usort($emails, function (string $a, string $b) {
            $aInst = $this->domainMatches(Str::after($a, '@'), $this->domainList('institutional'));
            $bInst = $this->domainMatches(Str::after($b, '@'), $this->domainList('institutional'));

            return $bInst <=> $aInst;
        });

        return array_values(array_unique($emails));
    }

    /**
     * @param  list<string>  $patterns
     */
    protected function domainMatches(string $domain, array $patterns): bool
    {
        $domain = Str::lower(ltrim($domain, '.'));

        foreach ($patterns as $pattern) {
            $pattern = Str::lower(ltrim(trim($pattern), '.'));

            if ($pattern === '') {
                continue;
            }

            if ($domain === $pattern) {
                return true;
            }

            if (str_ends_with($domain, '.'.$pattern)) {
                return true;
            }

            // Suffix tokens like "edu", "ac.in", "gov"
            if (! str_contains($pattern, '.') && str_ends_with($domain, '.'.$pattern)) {
                return true;
            }
        }

        return false;
    }

    protected function countryPoints(Person $person): int
    {
        $raw = $person->organization?->country_code ?: $person->country_code;

        if ($raw === null || trim((string) $raw) === '') {
            return 14;
        }

        $label = app(CountryLabelResolver::class)->resolve((string) $raw) ?? (string) $raw;
        $normalized = Str::lower(trim($label));

        foreach ([1 => 20, 2 => 16, 3 => 12, 4 => 8] as $tier => $points) {
            foreach ($this->countryTierList($tier) as $country) {
                if (Str::lower(trim($country)) === $normalized) {
                    return $points;
                }
            }
        }

        // Present but unlisted → Tier 3.
        return 12;
    }

    protected function profilePoints(Person $person): int
    {
        $education = Str::lower(trim((string) ($person->education_level ?? '')));

        if ($education === '') {
            return 6;
        }

        if (str_contains($education, 'faculty') || str_contains($education, 'phd') || str_contains($education, 'ph.d') || str_contains($education, 'doctor')) {
            return 20;
        }

        if (str_contains($education, 'industry') || str_contains($education, 'master')) {
            return 16;
        }

        if (str_contains($education, 'undergrad') || str_contains($education, 'bachelor')) {
            return 10;
        }

        return match ($person->education_level) {
            'Faculty', 'PhD' => 20,
            'Industry', 'Masters' => 16,
            'Undergraduate' => 10,
            default => 6,
        };
    }

    protected function bandFor(int $total): string
    {
        $hotMin = (int) $this->configValue('lead_score.bands.thresholds.hot_min', config('omicslogic.lead_score.bands.hot_min', 75));
        $warmMin = (int) $this->configValue('lead_score.bands.thresholds.warm_min', config('omicslogic.lead_score.bands.warm_min', 55));
        $nurtureMin = (int) $this->configValue('lead_score.bands.thresholds.nurture_min', config('omicslogic.lead_score.bands.nurture_min', 35));

        return match (true) {
            $total >= $hotMin => 'hot',
            $total >= $warmMin => 'warm',
            $total >= $nurtureMin => 'nurture',
            default => 'low',
        };
    }

    /**
     * @return list<string>
     */
    protected function domainList(string $key): array
    {
        $default = (string) config("omicslogic.lead_score.domains.{$key}", '');
        $value = (string) $this->configValue("lead_score.domains.lists.{$key}", $default);

        return $this->lines($value);
    }

    /**
     * @return list<string>
     */
    protected function countryTierList(int $tier): array
    {
        $default = (string) config("omicslogic.lead_score.country_tiers.tier{$tier}", '');
        $value = (string) $this->configValue("lead_score.country_tiers.lists.tier{$tier}", $default);

        return $this->lines($value);
    }

    /**
     * @return list<string>
     */
    protected function lines(string $value): array
    {
        return array_values(array_filter(array_map(
            fn (string $line) => trim($line),
            preg_split('/\r\n|\r|\n|,/', $value) ?: []
        )));
    }

    protected function configValue(string $path, mixed $default = null): mixed
    {
        if (function_exists('core')) {
            $configured = core()->getConfigData($path);

            if ($configured !== null && $configured !== '') {
                return $configured;
            }
        }

        return $default;
    }
}
