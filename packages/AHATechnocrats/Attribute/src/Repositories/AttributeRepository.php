<?php

namespace AHATechnocrats\Attribute\Repositories;

use AHATechnocrats\Attribute\Contracts\Attribute;
use AHATechnocrats\Core\Eloquent\Repository;
use Illuminate\Container\Container;
use Illuminate\Support\Str;

class AttributeRepository extends Repository
{
    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct(
        protected AttributeOptionRepository $attributeOptionRepository,
        Container $container
    ) {
        parent::__construct($container);
    }

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'AHATechnocrats\Attribute\Contracts\Attribute';
    }

    /**
     * @return Attribute
     */
    public function create(array $data)
    {
        $options = isset($data['options']) ? $data['options'] : [];

        $attribute = $this->model->create($data);

        if (in_array($attribute->type, ['select', 'multiselect', 'checkbox']) && count($options)) {
            $sortOrder = 1;

            foreach ($options as $optionInputs) {
                $this->attributeOptionRepository->create(array_merge([
                    'attribute_id' => $attribute->id,
                    'sort_order' => $sortOrder++,
                ], $optionInputs));
            }
        }

        return $attribute;
    }

    /**
     * @param  int  $id
     * @param  string  $attribute
     * @return Attribute
     */
    public function update(array $data, $id, $attribute = 'id')
    {
        $attribute = $this->find($id);

        $attribute->update($data);

        if (! in_array($attribute->type, ['select', 'multiselect', 'checkbox'])) {
            return $attribute;
        }

        if (! isset($data['options'])) {
            return $attribute;
        }

        foreach ($data['options'] as $optionId => $optionInputs) {
            $isNew = $optionInputs['isNew'] == 'true';

            if ($isNew) {
                $this->attributeOptionRepository->create(array_merge([
                    'attribute_id' => $attribute->id,
                ], $optionInputs));
            } else {
                $isDelete = $optionInputs['isDelete'] == 'true';

                if ($isDelete) {
                    $this->attributeOptionRepository->delete($optionId);
                } else {
                    $this->attributeOptionRepository->update($optionInputs, $optionId);
                }
            }
        }

        return $attribute;
    }

    /**
     * @param  string  $code
     * @return Attribute
     */
    public function getAttributeByCode($code)
    {
        static $attributes = [];

        if (array_key_exists($code, $attributes)) {
            return $attributes[$code];
        }

        return $attributes[$code] = $this->findOneByField('code', $code);
    }

    /**
     * @param  int  $lookup
     * @param  string  $query
     * @param  array  $columns
     * @return array
     */
    public function getLookUpOptions($lookup, $query = '', $columns = [])
    {
        $lookup = config('attribute_lookups.'.$lookup);

        $query = (string) ($query ?? '');

        if (! count($columns)) {
            $columns = [($lookup['value_column'] ?? 'id').' as id', ($lookup['label_column'] ?? 'name').' as name'];
        }

        if (Str::contains($lookup['repository'], 'UserRepository')) {
            $userRepository = app($lookup['repository'])->where('status', 1);

            $currentUser = auth()->guard('user')->user();

            if ($currentUser?->view_permission === 'group') {
                $query = urldecode($query);

                $userIds = bouncer()->getAuthorizedUserIds();

                return $userRepository
                    ->when(! empty($userIds), fn ($queryBuilder) => $queryBuilder->whereIn('users.id', $userIds))
                    ->when(! empty($query), fn ($queryBuilder) => $queryBuilder->where('users.name', 'like', "%{$query}%"))
                    ->get();
            } elseif ($currentUser?->view_permission === 'individual') {
                return $userRepository->where('users.id', $currentUser->id)->get();
            }

            return $userRepository->where('users.name', 'like', '%'.urldecode($query).'%')->get();
        }

        if (Str::contains($lookup['repository'], 'LeadRepository')) {
            return $this->getOwnerScopedLookupOptions($lookup, $query, $columns, ['user_id']);
        }

        if (Str::contains($lookup['repository'], 'PersonRepository')) {
            return $this->getOwnerScopedLookupOptions($lookup, $query, $columns, ['user_id'], function ($queryBuilder) {
                return $queryBuilder->whereNull('merged_into_id');
            });
        }

        if (Str::contains($lookup['repository'], 'OrganizationRepository')) {
            return $this->getOwnerScopedLookupOptions($lookup, $query, $columns, ['account_owner_id', 'user_id']);
        }

        return app($lookup['repository'])->findWhere([
            [$lookup['label_column'] ?? 'name', 'like', '%'.urldecode($query).'%'],
        ], $columns);
    }

    /**
     * Scope lookup results to records the current user is allowed to see.
     */
    protected function getOwnerScopedLookupOptions(
        array $lookup,
        ?string $query,
        array $columns,
        array $ownerColumns,
        ?callable $additionalScope = null
    ) {
        $query = (string) ($query ?? '');

        if (! count($columns)) {
            $columns = [
                ($lookup['value_column'] ?? 'id').' as id',
                ($lookup['label_column'] ?? 'name').' as name',
            ];
        }

        $labelColumn = $lookup['label_column'] ?? 'name';
        $decodedQuery = urldecode($query);

        $queryBuilder = app($lookup['repository'])->makeModel()->newQuery();

        if ($additionalScope) {
            $queryBuilder = $additionalScope($queryBuilder);
        }

        if ($decodedQuery !== '') {
            $queryBuilder->where($labelColumn, 'like', '%'.$decodedQuery.'%');
        }

        if ($userIds = bouncer()->getAuthorizedUserIds()) {
            $queryBuilder->where(function ($scope) use ($userIds, $ownerColumns) {
                foreach ($ownerColumns as $ownerColumn) {
                    $scope->orWhereIn($ownerColumn, $userIds);
                }
            });
        }

        return $queryBuilder->limit(25)->get($columns);
    }

    /**
     * @param  string  $lookup
     * @param  int|array  $entityId
     * @param  array  $columns
     * @return mixed
     */
    public function getLookUpEntity($lookup, $entityId = null, $columns = [])
    {
        if (! $entityId) {
            return;
        }

        $lookup = config('attribute_lookups.'.$lookup);

        if (! count($columns)) {
            $columns = [($lookup['value_column'] ?? 'id').' as id', ($lookup['label_column'] ?? 'name').' as name'];
        }

        if (is_array($entityId)) {
            return app($lookup['repository'])->findWhereIn(
                'id',
                $entityId,
                $columns
            );
        } else {
            return app($lookup['repository'])->find($entityId, $columns);
        }
    }
}
