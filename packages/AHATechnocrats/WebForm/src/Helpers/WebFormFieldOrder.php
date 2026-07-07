<?php

namespace AHATechnocrats\WebForm\Helpers;

use AHATechnocrats\WebForm\Contracts\WebForm as WebFormContract;
use AHATechnocrats\WebForm\Models\WebForm;
use AHATechnocrats\WebForm\Models\WebFormAttribute;

class WebFormFieldOrder
{
    public const INQUIRY_DETAILS = 'builtin:inquiry_details';

    /**
     * Person attribute codes rendered as built-in Omics form controls.
     *
     * @return list<string>
     */
    public static function omicsPersonFieldCodes(): array
    {
        return [
            'name',
            'emails',
            'email',
            'contact_numbers',
            'phone',
            'organization_name',
            'organization',
            'country_code',
            'country',
            'education_level',
            'education',
            'program_interest',
            'program_interest_other',
            'inquiry_details',
            'queries',
            'notes',
        ];
    }

    /**
     * @return array<string, array{label: string, locked: bool, removable: bool}>
     */
    public static function builtinFieldMeta(WebFormContract $webForm): array
    {
        $fields = [
            'builtin:name' => [
                'label' => 'Full Name',
                'locked' => true,
                'removable' => false,
            ],
            'builtin:email' => [
                'label' => 'Email Address',
                'locked' => true,
                'removable' => false,
            ],
            'builtin:phone' => [
                'label' => 'Phone number',
                'locked' => false,
                'removable' => false,
            ],
            'builtin:country' => [
                'label' => 'Country',
                'locked' => true,
                'removable' => false,
            ],
            'builtin:education' => [
                'label' => 'Level of Education',
                'locked' => false,
                'removable' => false,
            ],
            self::INQUIRY_DETAILS => [
                'label' => 'Any other details / queries',
                'locked' => false,
                'removable' => false,
                'pinned_last' => true,
            ],
        ];

        if ($webForm->organization_field && $webForm->organization_field !== 'none') {
            $fields['builtin:organization'] = [
                'label' => 'Company / Organization / University',
                'locked' => false,
                'removable' => false,
            ];
        }

        if (WebFormPrograms::isEnabled($webForm)) {
            $fields['builtin:program'] = [
                'label' => 'Interested in Campaign',
                'locked' => false,
                'removable' => false,
            ];
        }

        return $fields;
    }

    /**
     * @return list<string>
     */
    public static function defaultBuiltinOrder(WebFormContract $webForm): array
    {
        $order = [
            'builtin:name',
            'builtin:email',
        ];

        if ($webForm->organization_field && $webForm->organization_field !== 'none') {
            $order[] = 'builtin:organization';
        }

        $order[] = 'builtin:phone';
        $order[] = 'builtin:country';
        $order[] = 'builtin:education';

        if (WebFormPrograms::isEnabled($webForm)) {
            $order[] = 'builtin:program';
        }

        return $order;
    }

    /**
     * @return list<string>
     */
    public static function validKeys(WebFormContract $webForm): array
    {
        $keys = array_keys(self::builtinFieldMeta($webForm));

        foreach ($webForm->attributes()->with('attribute')->get() as $webFormAttribute) {
            if (! $webFormAttribute->attribute) {
                continue;
            }

            if (in_array($webFormAttribute->attribute->code, self::omicsPersonFieldCodes(), true)) {
                continue;
            }

            $keys[] = 'attribute:'.$webFormAttribute->id;
        }

        $keys[] = self::INQUIRY_DETAILS;

        return array_values(array_unique($keys));
    }

    /**
     * @param  list<string>  $order
     * @return list<string>
     */
    public static function normalizeOrder(WebFormContract $webForm, array $order): array
    {
        $order = array_values(array_unique(array_filter($order)));

        $order = array_values(array_filter(
            $order,
            fn (string $key) => $key !== self::INQUIRY_DETAILS
        ));

        $validKeys = self::validKeys($webForm);
        $validWithoutInquiry = array_values(array_filter(
            $validKeys,
            fn (string $key) => $key !== self::INQUIRY_DETAILS
        ));

        $normalized = [];

        foreach ($order as $key) {
            if (in_array($key, $validWithoutInquiry, true) && ! in_array($key, $normalized, true)) {
                $normalized[] = $key;
            }
        }

        foreach ($validWithoutInquiry as $key) {
            if (! in_array($key, $normalized, true)) {
                $normalized[] = $key;
            }
        }

        $normalized[] = self::INQUIRY_DETAILS;

        return $normalized;
    }

    /**
     * @return list<string>
     */
    public static function resolveOrder(WebFormContract $webForm): array
    {
        $stored = $webForm->field_order;

        if (is_string($stored)) {
            $stored = json_decode($stored, true);
        }

        if (! empty($stored) && is_array($stored)) {
            return self::normalizeOrder($webForm, $stored);
        }

        $order = self::defaultBuiltinOrder($webForm);

        foreach ($webForm->attributes()->with('attribute')->orderBy('sort_order')->get() as $webFormAttribute) {
            if (! $webFormAttribute->attribute) {
                continue;
            }

            if (in_array($webFormAttribute->attribute->code, self::omicsPersonFieldCodes(), true)) {
                continue;
            }

            $order[] = 'attribute:'.$webFormAttribute->id;
        }

        $order[] = self::INQUIRY_DETAILS;

        return self::normalizeOrder($webForm, $order);
    }

    /**
     * Build the default built-in editor fields for a brand new web form.
     *
     * Mirrors the fields the public form renders out of the box so the
     * create screen shows the same required fields as the edit screen.
     *
     * @return list<array<string, mixed>>
     */
    public static function defaultEditorFields(): array
    {
        $webForm = new WebForm([
            'organization_field' => 'required',
            'program_field' => 'required',
        ]);

        $builtinMeta = self::builtinFieldMeta($webForm);

        $order = self::defaultBuiltinOrder($webForm);
        $order[] = self::INQUIRY_DETAILS;

        $fields = [];

        foreach ($order as $key) {
            if (isset($builtinMeta[$key])) {
                $fields[] = array_merge([
                    'key' => $key,
                    'type' => 'builtin',
                ], $builtinMeta[$key]);
            }
        }

        return self::pinInquiryDetailsLastInEditorFields($fields);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function buildEditorFields(WebFormContract $webForm): array
    {
        $order = self::resolveOrder($webForm);
        $builtinMeta = self::builtinFieldMeta($webForm);
        $attributesByKey = [];

        foreach ($webForm->attributes()->with('attribute')->orderBy('sort_order')->get() as $webFormAttribute) {
            if (! $webFormAttribute->attribute) {
                continue;
            }

            if (in_array($webFormAttribute->attribute->code, self::omicsPersonFieldCodes(), true)) {
                continue;
            }

            $attributesByKey['attribute:'.$webFormAttribute->id] = self::formatAttributeField($webFormAttribute);
        }

        $fields = [];

        foreach ($order as $key) {
            if (str_starts_with($key, 'builtin:') && isset($builtinMeta[$key])) {
                $fields[] = array_merge([
                    'key' => $key,
                    'type' => 'builtin',
                ], $builtinMeta[$key]);

                continue;
            }

            if (isset($attributesByKey[$key])) {
                $fields[] = $attributesByKey[$key];
            }
        }

        foreach ($attributesByKey as $key => $field) {
            if (! collect($fields)->contains(fn (array $item) => ($item['key'] ?? null) === $key)) {
                $inquiryIndex = collect($fields)->search(
                    fn (array $item) => ($item['key'] ?? null) === self::INQUIRY_DETAILS
                );

                if ($inquiryIndex === false) {
                    $fields[] = $field;
                } else {
                    array_splice($fields, $inquiryIndex, 0, [$field]);
                }
            }
        }

        return self::pinInquiryDetailsLastInEditorFields($fields);
    }

    /**
     * @param  list<array<string, mixed>>  $fields
     * @return list<array<string, mixed>>
     */
    public static function pinInquiryDetailsLastInEditorFields(array $fields): array
    {
        $inquiry = null;
        $remaining = [];

        foreach ($fields as $field) {
            if (($field['key'] ?? null) === self::INQUIRY_DETAILS) {
                $inquiry = $field;

                continue;
            }

            $remaining[] = $field;
        }

        if ($inquiry) {
            $remaining[] = $inquiry;
        }

        return $remaining;
    }

    /**
     * @return array<string, mixed>
     */
    public static function formatAttributeField(WebFormAttribute $webFormAttribute): array
    {
        return [
            'key' => 'attribute:'.$webFormAttribute->id,
            'type' => 'attribute',
            'id' => $webFormAttribute->id,
            'name' => $webFormAttribute->name,
            'placeholder' => $webFormAttribute->placeholder,
            'is_required' => (bool) $webFormAttribute->is_required,
            'is_hidden' => (bool) $webFormAttribute->is_hidden,
            'attribute' => $webFormAttribute->attribute->toArray(),
        ];
    }

    public static function partialForKey(string $key): ?string
    {
        return match ($key) {
            'builtin:name' => 'builtin-name',
            'builtin:email' => 'builtin-email',
            'builtin:organization' => 'builtin-organization',
            'builtin:phone' => 'builtin-phone',
            'builtin:country' => 'builtin-country',
            'builtin:education' => 'builtin-education',
            'builtin:program' => 'builtin-program',
            self::INQUIRY_DETAILS => 'builtin-inquiry-details',
            default => null,
        };
    }

    /**
     * @param  list<string>  $fieldOrder
     */
    public static function replaceTemporaryAttributeKeys(array $fieldOrder, array $idMap): array
    {
        return array_map(function (string $key) use ($idMap) {
            if (! str_starts_with($key, 'attribute:')) {
                return $key;
            }

            $identifier = substr($key, strlen('attribute:'));

            if (isset($idMap[$identifier])) {
                return 'attribute:'.$idMap[$identifier];
            }

            return $key;
        }, $fieldOrder);
    }
}
