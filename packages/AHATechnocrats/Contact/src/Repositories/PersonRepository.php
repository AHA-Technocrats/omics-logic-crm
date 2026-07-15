<?php

namespace AHATechnocrats\Contact\Repositories;

use AHATechnocrats\Attribute\Repositories\AttributeRepository;
use AHATechnocrats\Attribute\Repositories\AttributeValueRepository;
use AHATechnocrats\Contact\Contracts\Person;
use AHATechnocrats\Core\Eloquent\Repository;
use AHATechnocrats\OmicsLogic\Services\LeadPersonSyncService;
use AHATechnocrats\OmicsLogic\Services\LeadScoreCalculator;
use AHATechnocrats\OmicsLogic\Services\OrganizationResolver;
use AHATechnocrats\OmicsLogic\Services\OwnerProfileImageService;
use AHATechnocrats\WebForm\Models\WebFormSubmission;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\DB;

class PersonRepository extends Repository
{
    /**
     * Searchable fields.
     */
    protected $fieldSearchable = [
        'name',
        'emails',
        'contact_numbers',
        'organization_id',
        'job_title',
        'organization.name',
        'user_id',
        'user.name',
    ];

    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct(
        protected AttributeRepository $attributeRepository,
        protected AttributeValueRepository $attributeValueRepository,
        protected OrganizationRepository $organizationRepository,
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
        return Person::class;
    }

    /**
     * Create.
     *
     * @return Person
     */
    public function create(array $data)
    {
        $data['entity_type'] = $data['entity_type'] ?? 'persons';

        $data = $this->sanitizeRequestedPersonData($data);

        if (! empty($data['organization_id'])) {
            if (! empty($data['organization'])) {
                $this->organizationRepository->update($data['organization'], $data['organization_id']);
            }
            unset($data['organization_name']);
        } elseif (! empty($data['organization_name'])) {
            $organization = app(OrganizationResolver::class)->resolve(
                $data['organization_name'],
                true,
                $data['country_code'] ?? null,
            );

            if ($organization && ! empty($data['organization'])) {
                $this->organizationRepository->update($data['organization'], $organization->id);
            }

            $data['organization_id'] = $organization?->id;
        }

        $data = $this->enrichOmicsLogicFields($data);

        if (isset($data['user_id'])) {
            $data['user_id'] = $data['user_id'] ?: null;
        }

        $person = parent::create($data);

        $person->lead_score = app(LeadScoreCalculator::class)->calculate($person);
        $person->save();

        $this->attributeValueRepository->save(array_merge($data, [
            'entity_id' => $person->id,
        ]));

        $this->syncLeadSideEffects($person, $data);

        $this->syncOwnerProfileImage($data);

        return $person;
    }

    /**
     * Update.
     *
     * @return Person
     */
    public function update(array $data, $id, $attributes = [])
    {
        $data['entity_type'] = $data['entity_type'] ?? 'persons';

        $existing = $this->find($id);

        $data = $this->sanitizeRequestedPersonData($data, $existing);

        if (array_key_exists('user_id', $data)) {
            $data['user_id'] = empty($data['user_id']) ? null : $data['user_id'];
        }

        if (! empty($data['organization_id'])) {
            if (! empty($data['organization'])) {
                $this->organizationRepository->update($data['organization'], $data['organization_id']);
            }
            unset($data['organization_name']);
        } elseif (! empty($data['organization_name'])) {
            $organization = app(OrganizationResolver::class)->resolve(
                $data['organization_name'],
                true,
                $data['country_code'] ?? null,
            );

            if ($organization && ! empty($data['organization'])) {
                $this->organizationRepository->update($data['organization'], $organization->id);
            }

            $data['organization_id'] = $organization?->id;

            unset($data['organization_name']);
        }

        $data = $this->enrichOmicsLogicFields($data, (int) $id);

        $person = parent::update($data, $id);

        $person->lead_score = app(LeadScoreCalculator::class)->calculate($person);
        $person->save();

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
                'entity_id' => $person->id,
            ]), $attributes);

            $this->syncLeadSideEffects($person, $data);

            $this->syncOwnerProfileImage($data, $person);

            return $person;
        }

        $this->attributeValueRepository->save(array_merge($data, [
            'entity_id' => $person->id,
        ]));

        $this->syncLeadSideEffects($person, $data);

        $this->syncOwnerProfileImage($data, $person);

        return $person;
    }

    protected function syncOwnerProfileImage(array $data, ?Person $person = null): void
    {
        $ownerId = (int) ($data['user_id'] ?? $person?->user_id ?? 0);

        app(OwnerProfileImageService::class)->syncFromRequest(
            'owner_profile_image',
            $ownerId,
            request()->isMethod('put')
                && ! request()->has('owner_profile_image')
                && ! request()->file('owner_profile_image'),
            ['persons.edit'],
        );
    }

    /**
     * Keep linked leads aligned when person CRM fields change.
     */
    private function syncLeadSideEffects(Person $person, array $data): void
    {
        $sync = app(LeadPersonSyncService::class);

        if (array_key_exists('user_id', $data)) {
            $sync->syncOwnerFromPerson($person->fresh());
        }

        if (
            array_key_exists('primary_product_id', $data)
            || array_key_exists('primary_source_id', $data)
        ) {
            $sync->syncPersonLeads($person->fresh());
        }
    }

    /**
     * Retrieves customers count based on date.
     *
     * @return int
     */
    public function getCustomerCount($startDate, $endDate)
    {
        return $this
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->count();
    }

    public function fetchOrCreateOrganizationByName(string $organizationName)
    {
        return app(OrganizationResolver::class)->resolve($organizationName, true);
    }

    /**
     * Normalize OmicsLogic-specific person fields before persistence.
     */
    private function enrichOmicsLogicFields(array $data, ?int $personId = null): array
    {
        foreach (['primary_product_id', 'primary_source_id', 'user_id', 'organization_id'] as $foreignKey) {
            if (array_key_exists($foreignKey, $data) && $data[$foreignKey] === '') {
                $data[$foreignKey] = null;
            }
        }

        if (array_key_exists('education_level', $data) && $data['education_level'] === '') {
            $data['education_level'] = null;
        }

        if (array_key_exists('is_student', $data)) {
            $data['is_student'] = (bool) $data['is_student'];
        }

        if ($organizationId = $data['organization_id'] ?? ($personId ? $this->find($personId)?->organization_id : null)) {
            if (array_key_exists('country_code', $data)) {
                DB::table('organizations')
                    ->where('id', $organizationId)
                    ->update(['country_code' => $data['country_code']]);
            }
        }

        $data = $this->syncCountryFromOrganization($data, $personId);

        $email = $data['emails'][0]['value'] ?? null;
        $phone = $data['contact_numbers'][0]['value'] ?? null;

        if ($email) {
            $data['normalized_email'] = strtolower(trim($email));
        }

        if ($phone) {
            $data['normalized_phone'] = preg_replace('/\D+/', '', $phone);
        }

        $data['last_activity_at'] = $data['last_activity_at'] ?? now();

        return $data;
    }

    private function syncCountryFromOrganization(array $data, ?int $personId = null): array
    {
        $organizationId = $data['organization_id'] ?? null;

        if (! $organizationId && $personId) {
            $organizationId = $this->find($personId)?->organization_id;
        }

        if ($organizationId) {
            $data['country_code'] = $this->organizationRepository->find($organizationId)?->country_code;
        } elseif (array_key_exists('organization_id', $data)) {
            $data['country_code'] = null;
        }

        return $data;
    }

    /**
     * Sanitize requested person data and return the clean array.
     *
     * When updating with a partial payload (e.g. only user_id), merge identity
     * fields from the existing person so unique_id is not collapsed to a bare
     * owner id that collides with other contacts.
     */
    private function sanitizeRequestedPersonData(array $data, $existing = null): array
    {
        if (
            array_key_exists('organization_id', $data)
            && empty($data['organization_id'])
        ) {
            $data['organization_id'] = null;
        }

        $userId = array_key_exists('user_id', $data)
            ? $data['user_id']
            : $existing?->user_id;

        $organizationId = array_key_exists('organization_id', $data)
            ? $data['organization_id']
            : $existing?->organization_id;

        $email = $data['emails'][0]['value']
            ?? ($existing?->emails[0]['value'] ?? null);

        $uniqueIdParts = array_filter([
            $userId ?: null,
            $organizationId ?: null,
            $email ?: null,
        ], fn ($part) => $part !== null && $part !== '');

        $data['unique_id'] = implode('|', $uniqueIdParts);

        if (isset($data['contact_numbers'])) {
            $data['contact_numbers'] = collect($data['contact_numbers'])
                ->filter(fn ($number) => filled($number['value'] ?? null))
                ->values()
                ->toArray();

            if (! empty($data['contact_numbers'])) {
                $data['unique_id'] .= '|'.$data['contact_numbers'][0]['value'];
            }
        } elseif (! empty($existing?->contact_numbers[0]['value'])) {
            $data['unique_id'] .= '|'.$existing->contact_numbers[0]['value'];
        }

        // Never persist a unique_id that is only a numeric owner id.
        if ($data['unique_id'] !== '' && ctype_digit((string) $data['unique_id'])) {
            unset($data['unique_id']);
        }

        return $data;
    }

    /**
     * Delete a person and cascade-remove linked records that block deletion.
     *
     * @param  int  $id
     * @return int
     */
    public function delete($id)
    {
        $person = $this->findOrFail($id);

        if ($person->leads()->exists()) {
            throw new \RuntimeException(trans('omicslogic::app.delete-timeline.person-has-leads'));
        }

        return DB::transaction(function () use ($id) {
            WebFormSubmission::query()
                ->where('person_id', $id)
                ->update(['person_id' => null]);

            $this->attributeValueRepository->deleteWhere([
                'entity_id' => $id,
                'entity_type' => 'persons',
            ]);

            return parent::delete($id);
        });
    }
}
