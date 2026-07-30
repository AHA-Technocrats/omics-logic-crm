<?php

namespace AHATechnocrats\OmicsLogic\Database\Seeders;

use AHATechnocrats\Contact\Models\Person;
use AHATechnocrats\Lead\Models\Lead;
use AHATechnocrats\Lead\Models\Product as LeadProduct;
use AHATechnocrats\Lead\Repositories\PipelineRepository;
use AHATechnocrats\Lead\Repositories\SourceRepository;
use AHATechnocrats\Lead\Repositories\TypeRepository;
use AHATechnocrats\OmicsLogic\Services\LeadScoreCalculator;
use AHATechnocrats\Product\Models\Product;
use AHATechnocrats\User\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds 8 demo Persons + Leads — 2 for each Score Band (Hot / Warm / Nurture / Low).
 *
 * php artisan db:seed --class="AHATechnocrats\\OmicsLogic\\Database\\Seeders\\LeadScoreBandDemoSeeder"
 */
class LeadScoreBandDemoSeeder extends Seeder
{
    public function run(): void
    {
        $ownerId = User::query()->orderBy('id')->value('id');
        $pipeline = app(PipelineRepository::class)->getDefaultPipeline();
        $stageId = $pipeline?->stages?->first()?->id;
        $sourceId = app(SourceRepository::class)->findOneByField('name', 'Web Form')?->id
            ?? app(SourceRepository::class)->first()?->id;
        $typeId = app(TypeRepository::class)->first()?->id;

        if (! $pipeline || ! $stageId) {
            $this->command?->error('Default pipeline/stage missing. Run CRM install seeders first.');

            return;
        }

        $campaigns = [
            30 => $this->campaign('Lead Score Demo — Research', 30),
            25 => $this->campaign('Lead Score Demo — Training', 25),
            20 => $this->campaign('Lead Score Demo — Workshop Purchased', 20),
            15 => $this->campaign('Lead Score Demo — Subscription', 15),
            10 => $this->campaign('Lead Score Demo — Workshop Inquiry', 10),
        ];

        $fixtures = [
            [
                'band' => 'hot',
                'name' => 'Hot Faculty US',
                'email' => 'hot.faculty.us@harvard.edu',
                'country' => 'United States',
                'education' => 'Faculty',
                'campaign' => 30,
            ],
            [
                'band' => 'hot',
                'name' => 'Hot PhD UK',
                'email' => 'hot.phd.uk@ox.ac.uk',
                'country' => 'United Kingdom',
                'education' => 'PhD',
                'campaign' => 25,
            ],
            [
                'band' => 'warm',
                'name' => 'Warm Masters India',
                'email' => 'warm.masters.india@gmail.com',
                'country' => 'India',
                'education' => 'Masters',
                'campaign' => 20,
            ],
            [
                'band' => 'warm',
                'name' => 'Warm Industry India',
                'email' => 'warm.industry.india@pfizer.com',
                'country' => 'India',
                'education' => 'Industry',
                'campaign' => 15,
            ],
            [
                'band' => 'nurture',
                'name' => 'Nurture Undergrad',
                'email' => 'nurture.undergrad@gmail.com',
                'country' => null,
                'education' => 'Undergraduate',
                'campaign' => 10,
            ],
            [
                'band' => 'nurture',
                'name' => 'Nurture Masters Nepal',
                'email' => 'nurture.masters.nepal@gmail.com',
                'country' => 'Nepal',
                'education' => 'Masters',
                'campaign' => 10,
            ],
            [
                'band' => 'low',
                'name' => 'Low Undergrad',
                'email' => 'low.undergrad@gmail.com',
                'country' => null,
                'education' => 'Undergraduate',
                'campaign' => null,
            ],
            [
                'band' => 'low',
                'name' => 'Low Other Iran',
                'email' => 'low.other.iran@gmail.com',
                'country' => 'Iran',
                'education' => 'Other',
                'campaign' => null,
            ],
        ];

        $calculator = app(LeadScoreCalculator::class);

        foreach ($fixtures as $fixture) {
            $campaign = $fixture['campaign'] !== null
                ? $campaigns[$fixture['campaign']]
                : null;

            $email = strtolower($fixture['email']);
            $phone = '90000000'.substr(md5($email), 0, 2);

            $person = Person::query()
                ->where('normalized_email', $email)
                ->first();

            if (! $person) {
                $person = new Person;
            }

            $person->fill([
                'name' => $fixture['name'],
                'emails' => [['value' => $email, 'label' => 'work']],
                'contact_numbers' => [['value' => $phone, 'label' => 'work']],
                'country_code' => $fixture['country'],
                'education_level' => $fixture['education'],
                'primary_product_id' => $campaign?->id,
                'primary_source_id' => $sourceId,
                'user_id' => $ownerId,
                'normalized_email' => $email,
                'normalized_phone' => $phone,
                'unique_id' => ($ownerId ?: '').'|'.$email.'|'.$phone,
                'last_activity_at' => now(),
            ]);
            $person->save();

            $title = 'Lead Score Demo — '.$fixture['band'].' — '.$fixture['name'];

            $lead = Lead::query()
                ->where('person_id', $person->id)
                ->where('title', $title)
                ->first();

            if (! $lead) {
                $lead = new Lead;
            }

            $lead->fill([
                'title' => $title,
                'description' => 'Demo lead for Score Band: '.strtoupper($fixture['band']),
                'lead_value' => 0,
                'status' => 1,
                'user_id' => $ownerId,
                'person_id' => $person->id,
                'lead_source_id' => $sourceId,
                'lead_type_id' => $typeId,
                'lead_pipeline_id' => $pipeline->id,
                'lead_pipeline_stage_id' => $stageId,
            ]);
            $lead->save();

            if ($campaign) {
                LeadProduct::query()->updateOrCreate(
                    [
                        'lead_id' => $lead->id,
                        'product_id' => $campaign->id,
                    ],
                    [
                        'price' => 0,
                        'quantity' => 1,
                        'amount' => 0,
                    ]
                );
            }

            $person = $person->fresh(['leads.products.product', 'primaryProduct', 'organization']);
            $calculator->applyToPerson($person);
            $person->save();

            $this->command?->info(sprintf(
                '%s → score %d · %s',
                $fixture['name'],
                (int) ($person->lead_score ?? 0),
                $person->score_band ?? 'n/a'
            ));
        }
    }

    protected function campaign(string $name, int $score): Product
    {
        $product = Product::query()->firstOrNew(['sku' => 'lead-score-demo-'.$score]);
        $product->fill([
            'name' => $name,
            'sku' => 'lead-score-demo-'.$score,
            'description' => 'Demo campaign for Lead Score Product Interest '.$score,
            'price' => 0,
            'quantity' => 0,
            'product_interest_score' => $score,
            'is_active' => true,
            'mapping_status' => 'mapped',
            'category' => 'Clinical',
        ]);
        $product->save();

        return $product;
    }
}
