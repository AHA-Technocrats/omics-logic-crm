<?php

namespace AHATechnocrats\OmicsLogic\Services;

use AHATechnocrats\Contact\Models\Person;
use AHATechnocrats\OmicsLogic\Enums\LifecycleStage;
use Carbon\Carbon;

class LeadScoreCalculator
{
    public function calculate(Person $person): int
    {
        $weights = config('omicslogic.lead_score');

        $profile = $this->profileScore($person);
        $engagement = $this->engagementScore($person);
        $intent = $person->primary_product_id ? 80 : 40;
        $recency = $this->recencyScore($person);

        $score = (
            $profile * $weights['profile_weight']
            + $engagement * $weights['engagement_weight']
            + $intent * $weights['intent_weight']
            + $recency * $weights['recency_weight']
        );

        return (int) min(100, max(0, round($score)));
    }

    protected function profileScore(Person $person): int
    {
        $score = 30;

        if ($person->education_level) {
            $score += 25;
        }

        if ($person->country_code) {
            $score += 20;
        }

        if ($person->organization_id) {
            $score += 25;
        }

        return min(100, $score);
    }

    protected function engagementScore(Person $person): int
    {
        $lessons = (int) $person->engagement_lessons;

        if ($lessons >= 10) {
            return 100;
        }

        if ($lessons >= 5) {
            return 75;
        }

        if ($lessons >= 1) {
            return 50;
        }

        return match ($person->lifecycle_stage) {
            LifecycleStage::Customer->value => 90,
            LifecycleStage::Engaged->value => 60,
            LifecycleStage::Lead->value => 25,
            default => 10,
        };
    }

    protected function recencyScore(Person $person): int
    {
        if (! $person->last_activity_at) {
            return 10;
        }

        $days = Carbon::parse($person->last_activity_at)->diffInDays(now());

        return match (true) {
            $days <= 7 => 100,
            $days <= 30 => 70,
            $days <= 90 => 40,
            default => 10,
        };
    }
}
