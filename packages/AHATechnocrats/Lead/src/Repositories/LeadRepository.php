<?php

namespace AHATechnocrats\Lead\Repositories;

use AHATechnocrats\Attribute\Repositories\AttributeRepository;
use AHATechnocrats\Attribute\Repositories\AttributeValueRepository;
use AHATechnocrats\Contact\Models\Organization;
use AHATechnocrats\Contact\Repositories\PersonRepository;
use AHATechnocrats\Core\Eloquent\Repository;
use AHATechnocrats\Lead\Contracts\Lead;
use AHATechnocrats\OmicsLogic\Services\LeadPersonSyncService;
use AHATechnocrats\OmicsLogic\Services\OrganizationAssigneeResolver;
use AHATechnocrats\OmicsLogic\Services\OwnerProfileImageService;
use Carbon\Carbon;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LeadRepository extends Repository
{
    /**
     * Searchable fields.
     */
    protected $fieldSearchable = [
        'title',
        'lead_value',
        'status',
        'user_id',
        'user.name',
        'person_id',
        'person.name',
        'lead_source_id',
        'lead_type_id',
        'lead_pipeline_id',
        'lead_pipeline_stage_id',
        'created_at',
        'closed_at',
        'expected_close_date',
        'products.product.name',
    ];

    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct(
        protected StageRepository $stageRepository,
        protected PersonRepository $personRepository,
        protected ProductRepository $productRepository,
        protected AttributeRepository $attributeRepository,
        protected AttributeValueRepository $attributeValueRepository,
        Container $container
    ) {
        parent::__construct($container);
    }

    /**
     * Specify model class name.
     *
     * @return mixed
     */
    public function model()
    {
        return Lead::class;
    }

    /**
     * Get leads query.
     *
     * @param  int  $pipelineId
     * @param  int  $pipelineStageId
     * @param  string  $term
     * @param  string  $createdAtRange
     * @return mixed
     */
    public function getLeadsQuery($pipelineId, $pipelineStageId, $term, $createdAtRange)
    {
        return $this->with([
            'attribute_values',
            'pipeline',
            'stage',
        ])->scopeQuery(function ($query) use ($pipelineId, $pipelineStageId, $term, $createdAtRange) {
            return $query->select(
                'leads.id as id',
                'leads.created_at as created_at',
                'title',
                'lead_value',
                'persons.name as person_name',
                'leads.person_id as person_id',
                'lead_pipelines.id as lead_pipeline_id',
                'lead_pipeline_stages.name as status',
                'lead_pipeline_stages.id as lead_pipeline_stage_id'
            )
                ->addSelect(DB::raw('DATEDIFF('.DB::getTablePrefix().'leads.created_at + INTERVAL lead_pipelines.rotten_days DAY, now()) as rotten_days'))
                ->leftJoin('persons', 'leads.person_id', '=', 'persons.id')
                ->leftJoin('lead_pipelines', 'leads.lead_pipeline_id', '=', 'lead_pipelines.id')
                ->leftJoin('lead_pipeline_stages', 'leads.lead_pipeline_stage_id', '=', 'lead_pipeline_stages.id')
                ->where('title', 'like', "%$term%")
                ->where('leads.lead_pipeline_id', $pipelineId)
                ->where('leads.lead_pipeline_stage_id', $pipelineStageId)
                ->when($createdAtRange, function ($query) use ($createdAtRange) {
                    return $query->whereBetween('leads.created_at', $createdAtRange);
                })
                ->where(function ($query) {
                    if ($userIds = bouncer()->getAuthorizedUserIds()) {
                        $query->whereIn('leads.user_id', $userIds);
                    }
                });
        });
    }

    /**
     * Create.
     *
     * @return Lead
     */
    public function create(array $data)
    {
        /**
         * If a person is provided, create or update the person and set the `person_id`.
         */
        if (isset($data['person'])) {
            $person = $this->persistPerson($data['person']);

            $data['person_id'] = $person->id;
        }

        $data['user_id'] = $this->resolveLeadOwnerId($data);

        if (empty($data['expected_close_date'])) {
            $data['expected_close_date'] = null;
        }

        $lead = parent::create(array_merge([
            'lead_pipeline_id' => 1,
            'lead_pipeline_stage_id' => 1,
        ], $data));

        $this->attributeValueRepository->save(array_merge($data, [
            'entity_id' => $lead->id,
        ]));

        if (isset($data['products'])) {
            foreach ($data['products'] as $product) {
                $this->productRepository->create(array_merge($product, [
                    'lead_id' => $lead->id,
                    'amount' => $product['price'] * $product['quantity'],
                ]));
            }
        } elseif ($lead->person_id) {
            $person = $this->personRepository->find($lead->person_id);

            if ($person) {
                $sync = app(LeadPersonSyncService::class);

                if (array_key_exists('user_id', $data) && $data['user_id']) {
                    $sync->syncOwnerFromLead($lead);
                } elseif ($person->user_id) {
                    $lead->update(['user_id' => $person->user_id]);
                }

                $sync->syncLead($lead->fresh(), $person->fresh());
            }
        }

        app(LeadPersonSyncService::class)->syncLifecycleStageFromLead($lead->fresh());

        return $lead;
    }

    /**
     * Update.
     *
     * @param  int  $id
     * @param  array|Collection  $attributes
     * @return Lead
     */
    public function update(array $data, $id, $attributes = [])
    {
        /**
         * If a person is provided, create or update the person and set the `person_id`.
         * Be cautious, as a lead can be updated without providing person data.
         * For example, in the lead Kanban section, when switching stages, only the stage will be updated.
         */
        if (isset($data['person'])) {
            $person = $this->persistPerson($data['person']);

            $data['person_id'] = $person->id;
        }

        if (isset($data['lead_pipeline_stage_id'])) {
            $stage = $this->stageRepository->find($data['lead_pipeline_stage_id']);

            if (in_array($stage->code, ['won', 'lost'])) {
                $data['closed_at'] = $data['closed_at'] ?? Carbon::now();
            } else {
                $data['closed_at'] = null;
            }
        }

        if (empty($data['expected_close_date'])) {
            $data['expected_close_date'] = null;
        }

        $lead = parent::update($data, $id);

        /**
         * If attributes are provided, only save the provided attributes and return.
         * A collection of attributes may also be provided, which will be treated as valid,
         * regardless of whether it is empty or not.
         */
        if (! empty($attributes)) {
            /**
             * If attributes are provided as an array, then fetch the attributes from the database;
             * otherwise, use the provided collection of attributes.
             */
            if (is_array($attributes)) {
                $conditions = ['entity_type' => $data['entity_type']];

                if (isset($data['quick_add'])) {
                    $conditions['quick_add'] = 1;
                }

                $attributes = $this->attributeRepository->where($conditions)
                    ->whereIn('code', $attributes)
                    ->get();
            }

            $this->attributeValueRepository->save(array_merge($data, [
                'entity_id' => $lead->id,
            ]), $attributes);

            $this->syncPersonSideEffects($lead, $data);

            return $lead;
        }

        $this->attributeValueRepository->save(array_merge($data, [
            'entity_id' => $lead->id,
        ]));

        $previousProductIds = $lead->products()->pluck('id');

        if (isset($data['products'])) {
            foreach ($data['products'] as $productId => $productInputs) {
                if (Str::contains($productId, 'product_')) {
                    $this->productRepository->create(array_merge([
                        'lead_id' => $lead->id,
                    ], $productInputs));
                } else {
                    if (is_numeric($index = $previousProductIds->search($productId))) {
                        $previousProductIds->forget($index);
                    }

                    $this->productRepository->update($productInputs, $productId);
                }
            }
        }

        foreach ($previousProductIds as $productId) {
            $this->productRepository->delete($productId);
        }

        $this->syncPersonSideEffects($lead, $data);

        $this->syncLeadOwnerProfileImage($data, $lead);

        return $lead;
    }

    protected function syncLeadOwnerProfileImage(array $data, Lead $lead): void
    {
        $ownerId = (int) ($data['user_id'] ?? $lead->user_id ?? 0);

        app(OwnerProfileImageService::class)->syncFromRequest(
            'lead_owner_image',
            $ownerId,
            request()->isMethod('put')
                && ! request()->has('lead_owner_image')
                && ! request()->file('lead_owner_image'),
            ['leads.edit'],
        );
    }

    /**
     * Keep the linked person aligned when lead CRM fields change.
     */
    protected function syncPersonSideEffects(Lead $lead, array $data): void
    {
        if (array_key_exists('user_id', $data)) {
            app(LeadPersonSyncService::class)->syncOwnerFromLead($lead->fresh());
        }

        app(LeadPersonSyncService::class)->syncLifecycleStageFromLead($lead->fresh());
    }

    /**
     * Create or update the linked person record from lead form data.
     */
    protected function persistPerson(array $personData)
    {
        $personPayload = array_merge($personData, [
            'entity_type' => 'persons',
        ]);

        if (! empty($personData['id'])) {
            return $this->personRepository->update($personPayload, $personData['id']);
        }

        return $this->personRepository->create($personPayload);
    }

    /**
     * Assign every new lead to an owner: explicit value, linked contact,
     * organization account owner, or the super admin — never leave unassigned.
     */
    protected function resolveLeadOwnerId(array $data): ?int
    {
        if (! empty($data['user_id'])) {
            return (int) $data['user_id'];
        }

        $person = ! empty($data['person_id'])
            ? $this->personRepository->find($data['person_id'])
            : null;

        if ($person?->user_id) {
            return (int) $person->user_id;
        }

        $organization = $person?->organization_id
            ? Organization::query()->find($person->organization_id)
            : null;

        $ownerId = app(OrganizationAssigneeResolver::class)->resolve($organization);

        if ($organization && empty($organization->account_owner_id) && $ownerId) {
            $organization->account_owner_id = $ownerId;
            $organization->save();
        }

        return $ownerId;
    }
}
