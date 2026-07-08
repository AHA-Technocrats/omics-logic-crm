<?php

namespace App\Firebase\Services;

class FirestoreFormMapper
{
    /**
     * @param  array<string, mixed>  $document
     * @return array{persons: array<string, mixed>, leads: array<string, mixed>}
     */
    public function toSubmissionInput(array $document): array
    {
        $fieldMap = (array) config('firebase.forms.field_map', []);

        $person = [];

        foreach ($fieldMap as $target => $sources) {
            $value = $this->firstValue($document, (array) $sources);

            if ($value === null) {
                continue;
            }

            match ($target) {
                'email' => $person['emails'] = [['value' => $value, 'label' => 'work']],
                'phone' => $person['contact_numbers'] = [['value' => $value, 'label' => 'work']],
                'organization' => $person['organization_name'] = $value,
                default => $person[$target] = $value,
            };
        }

        if (empty($person['name'])) {
            $first = $this->firstValue($document, ['firstName', 'first_name']);
            $last = $this->firstValue($document, ['lastName', 'last_name']);
            $combined = trim(trim((string) $first).' '.trim((string) $last));

            if ($combined !== '') {
                $person['name'] = $combined;
            }
        }

        $name = $person['name'] ?? 'Website Form';

        return [
            'persons' => $person,
            'leads' => [
                'title' => 'Website Form — '.$name,
                'description' => $person['inquiry_details'] ?? null,
            ],
            'firestore_doc_id' => (string) ($document['id'] ?? ''),
        ];
    }

    /**
     * @param  array<string, mixed>  $document
     * @param  array<int, string>  $keys
     */
    protected function firstValue(array $document, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (! isset($document[$key])) {
                continue;
            }

            $value = $document[$key];

            if (is_array($value)) {
                $encoded = collect($value)
                    ->filter(fn ($item) => is_scalar($item) && trim((string) $item) !== '')
                    ->map(fn ($item) => trim((string) $item))
                    ->implode(', ');

                if ($encoded !== '') {
                    return $encoded;
                }

                continue;
            }

            if (is_scalar($value) && trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }

        return null;
    }
}
