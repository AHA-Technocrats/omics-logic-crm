<?php

namespace AHATechnocrats\OmicsLogic\Console\Commands;

use AHATechnocrats\Contact\Models\Person;
use AHATechnocrats\OmicsLogic\Services\LeadScoreCalculator;
use Illuminate\Console\Command;

class RescorePersonsCommand extends Command
{
    protected $signature = 'omicslogic:rescore-persons {--chunk=200 : Persons per chunk}';

    protected $description = 'Recompute Lead Score, factor breakdown, and Score Band for all Persons';

    public function handle(LeadScoreCalculator $calculator): int
    {
        $chunk = max(1, (int) $this->option('chunk'));
        $processed = 0;

        Person::query()
            ->whereNull('merged_into_id')
            ->orderBy('id')
            ->chunkById($chunk, function ($persons) use ($calculator, &$processed) {
                foreach ($persons as $person) {
                    $calculator->applyToPerson($person);
                    $person->save();
                    $processed++;
                }

                $this->info("Processed {$processed} persons...");
            });

        $this->info("Done. Re-scored {$processed} persons.");

        return self::SUCCESS;
    }
}
