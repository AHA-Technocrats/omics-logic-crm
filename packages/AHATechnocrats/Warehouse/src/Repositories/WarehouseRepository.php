<?php

namespace AHATechnocrats\Warehouse\Repositories;

use AHATechnocrats\Attribute\Repositories\AttributeRepository;
use AHATechnocrats\Attribute\Repositories\AttributeValueRepository;
use AHATechnocrats\Core\Eloquent\Repository;
use AHATechnocrats\Warehouse\Contracts\Warehouse;
use Illuminate\Container\Container;

class WarehouseRepository extends Repository
{
    /**
     * Searchable fields.
     */
    protected $fieldSearchable = [
        'name',
        'contact_name',
        'contact_emails',
        'contact_numbers',
        'contact_address',
    ];

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
        return Warehouse::class;
    }

    /**
     * Create.
     *
     * @return Warehouse
     */
    public function create(array $data)
    {
        $warehouse = parent::create($data);

        $this->attributeValueRepository->save(array_merge($data, [
            'entity_id' => $warehouse->id,
        ]));

        return $warehouse;
    }

    /**
     * Update.
     *
     * @param  int  $id
     * @param  array  $attribute
     * @return Warehouse
     */
    public function update(array $data, $id, $attributes = [])
    {
        $warehouse = parent::update($data, $id);

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
                'entity_id' => $warehouse->id,
            ]), $attributes);

            return $warehouse;
        }

        $this->attributeValueRepository->save(array_merge($data, [
            'entity_id' => $warehouse->id,
        ]));

        return $warehouse;
    }
}
