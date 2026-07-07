<?php

namespace AHATechnocrats\Contact\Repositories;

use AHATechnocrats\Attribute\Repositories\AttributeRepository;
use AHATechnocrats\Attribute\Repositories\AttributeValueRepository;
use AHATechnocrats\Contact\Contracts\Organization;
use AHATechnocrats\Core\Eloquent\Repository;
use AHATechnocrats\OmicsLogic\Services\LeadPersonSyncService;
use AHATechnocrats\OmicsLogic\Services\OrganizationNormalizer;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\DB;

class OrganizationRepository extends Repository
{
    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct(
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
        return Organization::class;
    }

    /**
     * Create.
     *
     * @return Organization
     */
    public function create(array $data)
    {
        if (isset($data['user_id'])) {
            $data['user_id'] = $data['user_id'] ?: null;
        }

        if (isset($data['account_owner_id'])) {
            $data['account_owner_id'] = $data['account_owner_id'] ?: null;
        }

        $organization = parent::create($data);

        if (empty($organization->normalized_name) && ! empty($organization->name)) {
            $organization->normalized_name = app(OrganizationNormalizer::class)
                ->normalize($organization->name);
            $organization->save();
        }

        $this->attributeValueRepository->save(array_merge($data, [
            'entity_id' => $organization->id,
        ]));

        if (array_key_exists('account_owner_id', $data)) {
            app(LeadPersonSyncService::class)->syncOwnerFromOrganization($organization);
        }

        return $organization;
    }

    /**
     * Update.
     *
     * @param  int  $id
     * @param  array  $attribute
     * @return Organization
     */
    public function update(array $data, $id, $attributes = [])
    {
        if (isset($data['user_id'])) {
            $data['user_id'] = $data['user_id'] ?: null;
        }

        if (isset($data['account_owner_id'])) {
            $data['account_owner_id'] = $data['account_owner_id'] ?: null;
        }

        $organization = parent::update($data, $id);

        if (array_key_exists('country_code', $data)) {
            DB::table('persons')
                ->where('organization_id', $id)
                ->whereNull('merged_into_id')
                ->update(['country_code' => $data['country_code']]);
        }

        if (array_key_exists('account_owner_id', $data)) {
            app(LeadPersonSyncService::class)->syncOwnerFromOrganization($organization->fresh());
        }

        /**
         * If attributes are provided then only save the provided attributes and return.
         */
        if (! empty($attributes)) {
            $conditions = ['entity_type' => $data['entity_type']];

            if (isset($data['quick_add'])) {
                $conditions['quick_add'] = 1;
            }

            $attributes = $this->attributeRepository->where($conditions)
                ->whereIn('code', $attributes)
                ->get();

            $this->attributeValueRepository->save(array_merge($data, [
                'entity_id' => $organization->id,
            ]), $attributes);

            return $organization;
        }

        if (! empty($data['entity_type'])) {
            $this->attributeValueRepository->save(array_merge($data, [
                'entity_id' => $organization->id,
            ]));
        }

        return $organization;
    }

    /**
     * Delete organization and it's persons.
     *
     * @param  int  $id
     * @return @void
     */
    public function delete($id)
    {
        $organization = $this->findOrFail($id);

        DB::transaction(function () use ($organization, $id) {
            $this->attributeValueRepository->deleteWhere([
                'entity_id' => $id,
                'entity_type' => 'organizations',
            ]);

            $organization->delete();
        });
    }
}
