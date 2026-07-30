<?php

namespace AHATechnocrats\OmicsLogic\Jobs;

use AHATechnocrats\Contact\Repositories\PersonRepository;
use AHATechnocrats\Lead\Models\Product as LeadProduct;
use AHATechnocrats\OmicsLogic\Services\LeadScoreCalculator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RescorePersonsForCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $campaignId) {}

    public function handle(
        PersonRepository $personRepository,
        LeadScoreCalculator $calculator,
    ): void {
        $personIds = LeadProduct::query()
            ->where('product_id', $this->campaignId)
            ->join('leads', 'leads.id', '=', 'lead_products.lead_id')
            ->whereNotNull('leads.person_id')
            ->distinct()
            ->pluck('leads.person_id');

        $primaryPersonIds = $personRepository->getModel()
            ->newQuery()
            ->where('primary_product_id', $this->campaignId)
            ->pluck('id');

        $ids = $personIds->merge($primaryPersonIds)->unique()->filter()->values();

        foreach ($ids as $personId) {
            $person = $personRepository->find($personId);

            if (! $person) {
                continue;
            }

            $calculator->applyToPerson($person);
            $person->save();
        }
    }
}
