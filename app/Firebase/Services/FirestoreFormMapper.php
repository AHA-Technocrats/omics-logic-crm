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
        if (isset($document['feedbacks']) && is_array($document['feedbacks'])) {
            foreach ($document['feedbacks'] as $feedback) {
                if (is_array($feedback) && isset($feedback['question'], $feedback['answer'])) {
                    $document[$feedback['question']] = $feedback['answer'];
                }
            }
        }

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
                // Portal phone object: prefer E.164 / international formats.
                if (isset($value['e164Number']) || isset($value['number']) || isset($value['internationalNumber'])) {
                    foreach (['e164Number', 'internationalNumber', 'number'] as $phoneKey) {
                        if (! empty($value[$phoneKey]) && is_scalar($value[$phoneKey]) && trim((string) $value[$phoneKey]) !== '') {
                            return trim((string) $value[$phoneKey]);
                        }
                    }
                }

                $encoded = collect($value)
                    ->map(function ($item) {
                        if (is_array($item)) {
                            if (isset($item['question']) && isset($item['answer'])) {
                                return $item['question'].': '.$item['answer'];
                            }

                            return implode(', ', array_filter($item, 'is_scalar'));
                        }

                        return $item;
                    })
                    ->filter(fn ($item) => is_scalar($item) && trim((string) $item) !== '')
                    ->map(fn ($item) => trim((string) $item))
                    ->implode("\n");

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
