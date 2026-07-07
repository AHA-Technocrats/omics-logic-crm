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
            if (! is_scalar($value) || trim((string) $value) === '') {
                continue;
            }

            $rows[] = [
                'label' => ucfirst(str_replace('_', ' ', (string) $key)),
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
