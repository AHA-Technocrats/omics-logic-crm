<?php

namespace AHATechnocrats\OmicsLogic\Services;

use AHATechnocrats\Contact\Models\Person;
use AHATechnocrats\Contact\Repositories\PersonRepository;
use AHATechnocrats\Lead\Models\Lead;
use AHATechnocrats\Lead\Repositories\ProductRepository as LeadProductRepository;
use AHATechnocrats\Product\Models\Product;

class LeadPersonSyncService
{
    private static bool $syncingOwner = false;

    public function __construct(protected LeadProductRepository $leadProductRepository) {}

    public function syncPersonLeads(Person $person): void
    {
        Lead::query()
            ->where('person_id', $person->id)
            ->each(fn (Lead $lead) => $this->syncLead($lead, $person));
    }

    public function syncLead(Lead $lead, Person $person): void
    {
        $this->syncCampaign($lead, $person);
        $this->syncSource($lead, $person);
    }

    public function syncOwnerFromPerson(Person $person): void
    {
        if (self::$syncingOwner || ! $person->id) {
            return;
        }

        self::$syncingOwner = true;

        try {
            Lead::query()
                ->where('person_id', $person->id)
                ->where(function ($query) use ($person) {
                    $query->whereNull('user_id')
                        ->orWhere('user_id', '!=', $person->user_id);
                })
                ->update(['user_id' => $person->user_id]);
        } finally {
            self::$syncingOwner = false;
        }
    }

    public function syncOwnerFromLead(Lead $lead): void
    {
        if (self::$syncingOwner || ! $lead->person_id) {
            return;
        }

        $person = Person::query()->find($lead->person_id);

        if (! $person || $person->user_id === $lead->user_id) {
            return;
        }

        self::$syncingOwner = true;

        try {
            app(PersonRepository::class)->update([
                'entity_type' => 'persons',
                'user_id' => $lead->user_id,
            ], $person->id, ['user_id']);
        } finally {
            self::$syncingOwner = false;
        }
    }

    public function syncCampaign(Lead $lead, Person $person): void
    {
        if (! $person->primary_product_id) {
            return;
        }

        $product = Product::query()->find($person->primary_product_id);

        if (! $product) {
            return;
        }

        $price = (float) ($product->price ?? 0);

        $lead->products()
            ->where('product_id', '!=', $person->primary_product_id)
            ->delete();

        if ($lead->products()->where('product_id', $person->primary_product_id)->exists()) {
            return;
        }

        $this->leadProductRepository->create([
            'lead_id' => $lead->id,
            'product_id' => $person->primary_product_id,
            'price' => $price,
            'quantity' => 1,
            'amount' => $price,
        ]);
    }

    public function syncSource(Lead $lead, Person $person): void
    {
        if (! $person->primary_source_id) {
            return;
        }

        if ($lead->lead_source_id === $person->primary_source_id) {
            return;
        }

        $lead->update(['lead_source_id' => $person->primary_source_id]);
    }
}
