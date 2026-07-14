<?php

namespace AHATechnocrats\WebForm\Repositories;

use AHATechnocrats\Attribute\Repositories\AttributeRepository;
use AHATechnocrats\Core\Eloquent\Repository;
use AHATechnocrats\WebForm\Contracts\WebForm;
use AHATechnocrats\WebForm\Helpers\WebFormCampaigns;
use AHATechnocrats\WebForm\Helpers\WebFormFieldOrder;
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

        if (empty($data['thank_you_content'])) {
            $data['thank_you_content'] = \AHATechnocrats\WebForm\Models\WebForm::DEFAULT_THANK_YOU_CONTENT;
        }

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
                    $options = $this->normalizeCustomAttributeOptions(
                        $attributeData['options'] ?? [],
                        $attributeData['type'] ?? null
                    );

                    $attribute = $attributeRepository->create(array_filter([
                        'code' => $code,
                        'name' => $attributeData['name'],
                        'type' => $attributeData['type'],
                        'entity_type' => $attributeData['entity_type'],
                        'is_required' => $attributeData['is_required'] ?? 0,
                        'is_user_defined' => 1,
                        'quick_add' => 1,
                        'options' => $options,
                    ], fn ($value) => $value !== null));
                    $attributeData['attribute_id'] = $attribute->id;
                } else {
                    $attributeData['attribute_id'] = $existing->id;
                }

                // Remove temporary keys so they are not saved in web_form_attributes
                unset(
                    $attributeData['is_new'],
                    $attributeData['code'],
                    $attributeData['type'],
                    $attributeData['entity_type'],
                    $attributeData['options']
                );
            }
        }
    }

    /**
     * @return list<array{name: string}>
     */
    protected function normalizeCustomAttributeOptions(mixed $options, mixed $type): array
    {
        if (! in_array($type, ['checkbox', 'select', 'multiselect'], true)) {
            return [];
        }

        if (is_string($options)) {
            $options = json_decode($options, true);
        }

        if (! is_array($options)) {
            return [];
        }

        $normalized = [];

        foreach ($options as $option) {
            $name = is_array($option)
                ? trim((string) ($option['name'] ?? ''))
                : trim((string) $option);

            if ($name === '') {
                continue;
            }

            $normalized[] = ['name' => $name];
        }

        return $normalized;
    }

    protected function normalizeProgramSettings(array $data): array
    {
        // Campaign scope now owns program_options; keep this as a no-op for older callers.
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

        // Campaign interest is either on the form (always required) or hidden.
        // Legacy "optional" values are treated as shown/required.
        if ($data['program_field'] === 'optional') {
            $data['program_field'] = 'required';
        }

        if (! in_array($data['program_field'], ['none', 'required'], true)) {
            $data['program_field'] = 'required';
        }

        $data['is_active'] = filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $data['send_submitter_email'] = filter_var($data['send_submitter_email'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (array_key_exists('show_campaign_other', $data)) {
            $data['show_campaign_other'] = filter_var($data['show_campaign_other'], FILTER_VALIDATE_BOOLEAN);
        } else {
            $data['show_campaign_other'] = true;
        }

        if (($data['program_field'] ?? 'required') === 'none') {
            $data['show_campaign_other'] = false;
        }

        if (empty($data['email_template_id'])) {
            $data['email_template_id'] = null;
        }

        return $data;
    }

    /**
     * Persist customization drawer settings without a full form rewrite.
     */
    public function updateCustomization(int $id, array $data): WebForm
    {
        $webForm = $this->findOrFail($id);

        $payload = [
            'background_color' => $data['background_color'] ?? $webForm->background_color,
            'form_background_color' => $data['form_background_color'] ?? $webForm->form_background_color,
            'form_title_color' => $data['form_title_color'] ?? $webForm->form_title_color,
            'form_submit_button_color' => $data['form_submit_button_color'] ?? $webForm->form_submit_button_color,
            'attribute_label_color' => $data['attribute_label_color'] ?? $webForm->attribute_label_color,
            'program_field' => $data['program_field'] ?? $webForm->program_field,
            'campaign_scope' => $data['campaign_scope'] ?? $webForm->campaign_scope,
            'program_options' => $data['program_options'] ?? $webForm->program_options,
            'show_campaign_other' => array_key_exists('show_campaign_other', $data)
                ? $data['show_campaign_other']
                : $webForm->show_campaign_other,
            'allow_org_create' => array_key_exists('allow_org_create', $data)
                ? $data['allow_org_create']
                : $webForm->allow_org_create,
            'organization_field' => $webForm->organization_field ?? 'required',
        ];

        $payload = $this->normalizeCampaignSettings($payload);
        $payload = $this->normalizeRequiredFieldDefaults($payload);
        $payload['allow_org_create'] = filter_var($payload['allow_org_create'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $payload['show_campaign_other'] = filter_var($payload['show_campaign_other'] ?? true, FILTER_VALIDATE_BOOLEAN);

        if (($payload['program_field'] ?? 'required') === 'none') {
            $payload['show_campaign_other'] = false;
        }

        $fieldOrder = $this->parseFieldOrder($data['field_order'] ?? $webForm->field_order);

        $webForm->update($payload);

        if ($fieldOrder) {
            $fieldOrder = WebFormFieldOrder::normalizeOrder($webForm->fresh(), $fieldOrder);

            $webForm->update([
                'field_order' => $fieldOrder,
            ]);

            $this->syncAttributeSortOrders($webForm->fresh(), $fieldOrder);
        } else {
            // Re-normalize stored order so builtin:program is added/removed with program_field.
            $resolved = WebFormFieldOrder::resolveOrder($webForm->fresh());

            $webForm->update([
                'field_order' => $resolved,
            ]);
        }

        return $webForm->fresh();
    }
}
