<?php

namespace AHATechnocrats\WebForm\Repositories;

use AHATechnocrats\Attribute\Repositories\AttributeRepository;
use AHATechnocrats\Core\Eloquent\Repository;
use AHATechnocrats\WebForm\Contracts\WebForm;
use AHATechnocrats\WebForm\Helpers\WebFormCampaigns;
use AHATechnocrats\WebForm\Helpers\WebFormFieldOrder;
use AHATechnocrats\WebForm\Helpers\WebFormPrograms;
use Illuminate\Container\Container;
use Illuminate\Support\Str;

class WebFormRepository extends Repository
{
    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct(
        protected WebFormAttributeRepository $webFormAttributeRepository,
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
        return WebForm::class;
    }

    /**
     * Create Web Form.
     *
     * @return WebForm
     */
    public function create(array $data)
    {
        $this->createCustomAttributes($data);

        $data = $this->normalizeProgramSettings($data);
        $data = $this->normalizeCampaignSettings($data);
        $data = $this->normalizeRequiredFieldDefaults($data);

        $fieldOrder = $this->parseFieldOrder($data['field_order'] ?? null);

        unset($data['field_order']);

        $webForm = $this->model->create(array_merge($data, [
            'form_id' => Str::random(50),
        ]));

        $idMap = [];

        foreach ($data['attributes'] ?? [] as $attributeId => $attributeData) {
            $created = $this->webFormAttributeRepository->create(array_merge([
                'web_form_id' => $webForm->id,
            ], $attributeData));

            if (Str::contains((string) $attributeId, 'attribute_')) {
                $idMap[$attributeId] = $created->id;
            }
        }

        if ($fieldOrder) {
            $fieldOrder = WebFormFieldOrder::replaceTemporaryAttributeKeys($fieldOrder, $idMap);
            $fieldOrder = WebFormFieldOrder::normalizeOrder($webForm->fresh(), $fieldOrder);

            $webForm->update([
                'field_order' => $fieldOrder,
            ]);

            $this->syncAttributeSortOrders($webForm->fresh(), $fieldOrder);
        }

        return $webForm->fresh();
    }

    /**
     * Update Web Form.
     *
     * @param  int  $id
     * @param  string  $attribute
     * @return WebForm
     */
    public function update(array $data, $id, $attribute = 'id')
    {
        $this->createCustomAttributes($data);

        $data = $this->normalizeProgramSettings($data);
        $data = $this->normalizeCampaignSettings($data);
        $data = $this->normalizeRequiredFieldDefaults($data);

        $webForm = $this->find($id);

        $fieldOrder = $this->parseFieldOrder($data['field_order'] ?? null);

        unset($data['field_order']);

        $webForm = parent::update($data, $id);

        if (array_key_exists('attributes', $data)) {
            $previousAttributeIds = $webForm->attributes()->pluck('id');
            $idMap = [];

            foreach ($data['attributes'] as $attributeId => $attributeData) {
                if (Str::contains((string) $attributeId, 'attribute_')) {
                    $created = $this->webFormAttributeRepository->create(array_merge([
                        'web_form_id' => $webForm->id,
                    ], $attributeData));

                    $idMap[$attributeId] = $created->id;
                } else {
                    if (is_numeric($index = $previousAttributeIds->search($attributeId))) {
                        $previousAttributeIds->forget($index);
                    }

                    $this->webFormAttributeRepository->update($attributeData, $attributeId);
                }
            }

            foreach ($previousAttributeIds as $attributeId) {
                $this->webFormAttributeRepository->delete($attributeId);
            }

            if ($fieldOrder) {
                $fieldOrder = WebFormFieldOrder::replaceTemporaryAttributeKeys($fieldOrder, $idMap);
            }
        }

        if ($fieldOrder) {
            $fieldOrder = WebFormFieldOrder::normalizeOrder($webForm->fresh(), $fieldOrder);

            $webForm->update([
                'field_order' => $fieldOrder,
            ]);

            $this->syncAttributeSortOrders($webForm->fresh(), $fieldOrder);
        }

        return $webForm->fresh();
    }

    /**
     * @return list<string>|null
     */
    protected function parseFieldOrder(mixed $fieldOrder): ?array
    {
        if (empty($fieldOrder)) {
            return null;
        }

        if (is_string($fieldOrder)) {
            $fieldOrder = json_decode($fieldOrder, true);
        }

        return is_array($fieldOrder) ? array_values($fieldOrder) : null;
    }

    /**
     * @param  list<string>  $fieldOrder
     */
    protected function syncAttributeSortOrders(WebForm $webForm, array $fieldOrder): void
    {
        foreach ($fieldOrder as $index => $key) {
            if (! str_starts_with($key, 'attribute:')) {
                continue;
            }

            $attributeId = (int) substr($key, strlen('attribute:'));

            if ($attributeId <= 0) {
                continue;
            }

            $this->webFormAttributeRepository->update([
                'sort_order' => $index,
            ], $attributeId);
        }
    }

    protected function createCustomAttributes(array &$data): void
    {
        if (empty($data['attributes'])) {
            return;
        }

        $attributeRepository = app(AttributeRepository::class);

        foreach ($data['attributes'] as $key => &$attributeData) {
            if (! empty($attributeData['is_new'])) {
                // Ensure unique code
                $code = $attributeData['code'] ?? ('custom_'.Str::slug($attributeData['name'], '_').'_'.rand(1000, 9999));

                // Check if it already exists to avoid unique constraint violations
                $existing = $attributeRepository->findOneWhere([
                    'code' => $code,
                    'entity_type' => $attributeData['entity_type'],
                ]);

                if (! $existing) {
                    $attribute = $attributeRepository->create([
                        'code' => $code,
                        'name' => $attributeData['name'],
                        'type' => $attributeData['type'],
                        'entity_type' => $attributeData['entity_type'],
                        'is_required' => $attributeData['is_required'] ?? 0,
                        'is_user_defined' => 1,
                        'quick_add' => 1,
                    ]);
                    $attributeData['attribute_id'] = $attribute->id;
                } else {
                    $attributeData['attribute_id'] = $existing->id;
                }

                // Remove temporary keys so they are not saved in web_form_attributes
                unset($attributeData['is_new'], $attributeData['code'], $attributeData['type'], $attributeData['entity_type']);
            }
        }
    }

    protected function normalizeProgramSettings(array $data): array
    {
        if (array_key_exists('program_options', $data)) {
            $data['program_options'] = WebFormPrograms::normalizeOptionsInput($data['program_options']);
        }

        return $data;
    }

    protected function normalizeCampaignSettings(array $data): array
    {
        $scope = $data['campaign_scope'] ?? 'all';

        if (! in_array($scope, ['all', 'selected'], true)) {
            $scope = 'all';
        }

        $data['campaign_scope'] = $scope;

        if ($scope === 'selected') {
            $data['program_options'] = WebFormCampaigns::normalizeOptionsInput(
                $data['program_options'] ?? $data['campaign_options'] ?? null,
                'selected'
            );
        } else {
            $data['program_options'] = null;
        }

        unset($data['campaign_options']);

        return $data;
    }

    protected function normalizeRequiredFieldDefaults(array $data): array
    {
        $data['organization_field'] = $data['organization_field'] ?? 'required';
        $data['program_field'] = $data['program_field'] ?? 'required';

        if (! in_array($data['organization_field'], ['required'], true)) {
            $data['organization_field'] = 'required';
        }

        if (! in_array($data['program_field'], ['required'], true)) {
            $data['program_field'] = 'required';
        }

        $data['is_active'] = filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $data['send_submitter_email'] = filter_var($data['send_submitter_email'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (empty($data['email_template_id'])) {
            $data['email_template_id'] = null;
        }

        return $data;
    }
}
