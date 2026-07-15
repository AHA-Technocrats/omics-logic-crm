<?php

namespace AHATechnocrats\OmicsLogic\Services;

use AHATechnocrats\WebForm\Models\WebFormSubmission;
use Illuminate\Support\Arr;

class WebFormSubmissionPresenter
{
    /**
     * @return list<array{label: string, value: string}>
     */
    public function present(WebFormSubmission $submission): array
    {
        $payload = $submission->payload ?? [];
        $personData = Arr::wrap($payload['persons'] ?? []);
        $rows = [];

        $fieldMap = [
            'name' => 'Full name',
            'email' => 'Email address',
            'phone' => 'Phone number',
            'contact_numbers' => 'Phone number',
            'emails' => 'Email address',
            'organization_name' => 'Organization',
            'organization' => 'Organization',
            'country_code' => 'Country',
            'country' => 'Country',
            'education_level' => 'Education',
            'education' => 'Education',
            'program_interest' => 'Campaign',
            'program_interest_other' => 'Campaign (other)',
            'inquiry_details' => 'Other details / queries',
            'queries' => 'Other details / queries',
            'notes' => 'Notes',
        ];

        foreach ($fieldMap as $key => $label) {
            $value = $this->resolveValue($personData, $key);

            if ($value === null || $value === '') {
                continue;
            }

            $rows[] = [
                'label' => $label,
                'value' => $value,
            ];
        }

        foreach (Arr::wrap($payload['leads'] ?? []) as $key => $value) {
            if ($key === 'title') {
                continue;
            }

            if (! is_scalar($value) || trim((string) $value) === '') {
                continue;
            }

            $rows[] = [
                'label' => ucfirst(str_replace('_', ' ', (string) $key)),
                'value' => (string) $value,
            ];
        }

        $attributeRepository = app(\AHATechnocrats\Attribute\Repositories\AttributeRepository::class);

        foreach (Arr::wrap($payload['webforms'] ?? []) as $key => $value) {
            if (! is_scalar($value) && ! is_array($value)) {
                continue;
            }

            $attribute = $attributeRepository->findOneByField('code', $key);
            $label = $attribute ? $attribute->name : ucfirst(str_replace('_', ' ', (string) $key));

            if (is_array($value)) {
                if ($attribute && in_array($attribute->type, ['checkbox', 'multiselect'])) {
                    $mappedValues = [];
                    foreach ($value as $v) {
                        $option = $attribute->options()->where('id', $v)->first();
                        $mappedValues[] = $option ? $option->name : $v;
                    }
                    $value = implode(', ', array_filter($mappedValues, fn($v) => trim((string) $v) !== ''));
                } else {
                    $value = implode(', ', array_filter($value, fn($v) => trim((string) $v) !== ''));
                }
            } else {
                if ($attribute && in_array($attribute->type, ['select', 'radio', 'checkbox'])) {
                    $option = $attribute->options()->where('id', $value)->first();
                    if ($option) {
                        $value = $option->name;
                    }
                } elseif ($attribute && $attribute->type === 'boolean') {
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'Yes' : 'No';
                }
            }

            if (trim((string) $value) === '') {
                continue;
            }

            $rows[] = [
                'label' => $label,
                'value' => (string) $value,
            ];
        }

        return $rows;
    }

    protected function resolveValue(array $data, string $key): ?string
    {
        if ($key === 'emails') {
            return $data['emails'][0]['value'] ?? null;
        }

        if ($key === 'contact_numbers') {
            return $data['contact_numbers'][0]['value'] ?? null;
        }

        if (! isset($data[$key]) || ! is_scalar($data[$key])) {
            return null;
        }

        $value = trim((string) $data[$key]);

        if ($value === '' || $value === '__other__') {
            return null;
        }

        return $value;
    }
}
