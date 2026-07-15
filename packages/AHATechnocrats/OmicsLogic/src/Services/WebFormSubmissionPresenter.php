<?php

namespace AHATechnocrats\OmicsLogic\Services;

use AHATechnocrats\Attribute\Repositories\AttributeRepository;
use AHATechnocrats\Contact\Models\Person;
use AHATechnocrats\WebForm\Models\WebFormSubmission;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class WebFormSubmissionPresenter
{
    /**
     * Internal / nested payload keys that should not appear as standalone rows.
     *
     * @var list<string>
     */
    protected array $skipTopLevelKeys = [
        'persons',
        'leads',
        'webforms',
        'raw',
        'id',
        'firestore_doc_id',
        'feedbacks',
        'feedback',
        'userId',
        'programId',
        'courseId',
        'lessonId',
        'programSlug',
        'courseSlug',
        'lessonSlug',
        'submittedAt',
    ];

    /**
     * Preferred labels for known CRM / portal fields.
     *
     * @var array<string, string>
     */
    protected array $fieldMap = [
        'name' => 'Full name',
        'email' => 'Email address',
        'phone' => 'Phone number',
        'contact_numbers' => 'Phone number',
        'emails' => 'Email address',
        'organization_name' => 'Organization',
        'organization' => 'Organization',
        'company' => 'Organization',
        'country_code' => 'Country',
        'country' => 'Country',
        'education_level' => 'Education',
        'education' => 'Education',
        'program_interest' => 'Campaign',
        'program_interest_other' => 'Campaign (other)',
        'programTitle' => 'Campaign',
        'courseTitle' => 'Course',
        'lessonTitle' => 'Lesson',
        'inquiry_details' => 'Other details / queries',
        'queries' => 'Other details / queries',
        'notes' => 'Notes',
    ];

    /**
     * Build rows for the lead-view panel from a stored submission and/or person fields.
     *
     * @return list<array{label: string, value: string}>
     */
    public function presentForLead(?WebFormSubmission $submission, ?Person $person = null): array
    {
        if ($submission) {
            $rows = $this->present($submission);

            if ($person) {
                return $this->topUpMissingPersonFields($rows, $person);
            }

            return $rows;
        }

        return $person ? $this->presentPerson($person) : [];
    }

    /**
     * When a submission exists, only fill gaps (e.g. Campaign) from the person record.
     *
     * @param  list<array{label: string, value: string}>  $rows
     * @return list<array{label: string, value: string}>
     */
    protected function topUpMissingPersonFields(array $rows, Person $person): array
    {
        $seenLabels = [];
        $seenValues = [];

        foreach ($rows as $row) {
            $seenLabels[Str::lower(trim($row['label']))] = true;
            $seenValues[Str::lower(trim($row['value']))] = true;
        }

        $candidates = [
            'Campaign' => trim((string) ($person->program_interest ?? '')),
            'Education' => trim((string) ($person->education_level ?? '')),
            'Country' => trim((string) ($person->country_code ?? '')),
            'Organization' => trim((string) ($person->organization?->name ?? '')),
        ];

        foreach ($candidates as $label => $value) {
            if ($value === '') {
                continue;
            }

            $this->pushRow($rows, $seenLabels, $seenValues, $label, $value, dedupeByValue: true);
        }

        return $rows;
    }

    /**
     * @return list<array{label: string, value: string}>
     */
    public function present(WebFormSubmission $submission): array
    {
        $payload = $submission->payload ?? [];
        $personData = Arr::wrap($payload['persons'] ?? []);
        $rows = [];
        $seenLabels = [];
        $seenValues = [];
        $hasFeedbacks = ! empty($payload['feedbacks']) && is_array($payload['feedbacks']);

        foreach ($this->fieldMap as $key => $label) {
            if ($hasFeedbacks && in_array($key, ['inquiry_details', 'queries', 'notes'], true)) {
                continue;
            }

            $value = $this->resolveValue($personData, $key);

            if ($value === null || $value === '') {
                $value = $this->formatDisplayValue($payload[$key] ?? null);
            }

            if ($value === null || $value === '') {
                continue;
            }

            $this->pushRow($rows, $seenLabels, $seenValues, $label, $value);
        }

        foreach (Arr::wrap($payload['leads'] ?? []) as $key => $value) {
            if ($key === 'title') {
                continue;
            }

            $formatted = $this->formatDisplayValue($value);

            if ($formatted === null) {
                continue;
            }

            $this->pushRow(
                $rows,
                $seenLabels,
                $seenValues,
                $this->humanizeKey((string) $key),
                $formatted
            );
        }

        $attributeRepository = app(AttributeRepository::class);

        foreach (Arr::wrap($payload['webforms'] ?? []) as $key => $value) {
            if (! is_scalar($value) && ! is_array($value)) {
                continue;
            }

            $attribute = $attributeRepository->findOneByField('code', $key);
            $label = $attribute ? $attribute->name : $this->humanizeKey((string) $key);

            if (is_array($value)) {
                if ($attribute && in_array($attribute->type, ['checkbox', 'multiselect'])) {
                    $mappedValues = [];
                    foreach ($value as $v) {
                        $option = $attribute->options()->where('id', $v)->first();
                        $mappedValues[] = $option ? $option->name : $v;
                    }
                    $value = implode(', ', array_filter($mappedValues, fn ($v) => trim((string) $v) !== ''));
                } else {
                    $value = implode(', ', array_filter($value, fn ($v) => is_scalar($v) && trim((string) $v) !== ''));
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

            $this->pushRow($rows, $seenLabels, $seenValues, $label, (string) $value);
        }

        foreach ($personData as $key => $value) {
            if (array_key_exists($key, $this->fieldMap)) {
                continue;
            }

            $formatted = $this->formatDisplayValue($value);

            if ($formatted === null) {
                continue;
            }

            $this->pushRow(
                $rows,
                $seenLabels,
                $seenValues,
                $this->humanizeKey((string) $key),
                $formatted
            );
        }

        if ($hasFeedbacks) {
            foreach ($payload['feedbacks'] as $feedback) {
                if (! is_array($feedback) || ! isset($feedback['question'], $feedback['answer'])) {
                    continue;
                }

                $formatted = $this->formatDisplayValue($feedback['answer']);

                if ($formatted === null) {
                    continue;
                }

                $this->pushRow(
                    $rows,
                    $seenLabels,
                    $seenValues,
                    (string) $feedback['question'],
                    $formatted
                );
            }
        }

        if (! empty($payload['raw']) && is_array($payload['raw'])) {
            foreach ($payload['raw'] as $key => $value) {
                $formatted = $this->formatDisplayValue($value);

                if ($formatted === null) {
                    continue;
                }

                $this->pushRow(
                    $rows,
                    $seenLabels,
                    $seenValues,
                    (string) $key,
                    $formatted,
                    dedupeByValue: true
                );
            }
        }

        foreach ($payload as $key => $value) {
            if (in_array((string) $key, $this->skipTopLevelKeys, true)) {
                continue;
            }

            if (array_key_exists((string) $key, $this->fieldMap)) {
                continue;
            }

            $formatted = $this->formatDisplayValue($value);

            if ($formatted === null) {
                continue;
            }

            $this->pushRow(
                $rows,
                $seenLabels,
                $seenValues,
                $this->humanizeKey((string) $key),
                $formatted,
                dedupeByValue: true
            );
        }

        return $rows;
    }

    /**
     * Fallback rows from person columns when a submission row is missing or incomplete.
     *
     * @return list<array{label: string, value: string}>
     */
    public function presentPerson(Person $person): array
    {
        $rows = [];
        $seenLabels = [];
        $seenValues = [];

        $scalarFields = [
            'name' => 'Full name',
            'education_level' => 'Education',
            'program_interest' => 'Campaign',
            'country_code' => 'Country',
        ];

        foreach ($scalarFields as $attribute => $label) {
            $value = trim((string) ($person->{$attribute} ?? ''));

            if ($value === '') {
                continue;
            }

            $this->pushRow($rows, $seenLabels, $seenValues, $label, $value);
        }

        $email = $person->emails[0]['value'] ?? null;
        if (is_string($email) && trim($email) !== '') {
            $this->pushRow($rows, $seenLabels, $seenValues, 'Email address', trim($email));
        }

        $phone = $person->contact_numbers[0]['value'] ?? null;
        if (is_string($phone) && trim($phone) !== '') {
            $this->pushRow($rows, $seenLabels, $seenValues, 'Phone number', trim($phone));
        }

        $organization = $person->organization?->name;
        if (is_string($organization) && trim($organization) !== '') {
            $this->pushRow($rows, $seenLabels, $seenValues, 'Organization', trim($organization));
        }

        $inquiry = trim((string) ($person->inquiry_details ?? ''));

        if ($inquiry !== '') {
            $parsed = $this->parseInquiryLines($inquiry);

            if ($parsed !== []) {
                foreach ($parsed as $row) {
                    $this->pushRow($rows, $seenLabels, $seenValues, $row['label'], $row['value']);
                }
            } else {
                $this->pushRow($rows, $seenLabels, $seenValues, 'Other details / queries', $inquiry);
            }
        }

        return $rows;
    }

    /**
     * @return list<array{label: string, value: string}>
     */
    protected function parseInquiryLines(string $inquiry): array
    {
        $rows = [];

        foreach (preg_split("/\r\n|\n|\r/", $inquiry) as $line) {
            $line = trim($line);

            if ($line === '' || ! str_contains($line, ':')) {
                continue;
            }

            [$label, $value] = array_map('trim', explode(':', $line, 2));

            if ($label === '' || $value === '') {
                continue;
            }

            $rows[] = [
                'label' => $label,
                'value' => $value,
            ];
        }

        return $rows;
    }

    /**
     * @param  list<array{label: string, value: string}>  $rows
     * @param  array<string, true>  $seenLabels
     * @param  array<string, true>  $seenValues
     */
    protected function pushRow(
        array &$rows,
        array &$seenLabels,
        array &$seenValues,
        string $label,
        string $value,
        bool $dedupeByValue = false
    ): void {
        $labelKey = Str::lower(trim($label));
        $valueKey = Str::lower(trim($value));

        if ($labelKey === '' || $valueKey === '') {
            return;
        }

        if (isset($seenLabels[$labelKey])) {
            return;
        }

        if ($dedupeByValue && isset($seenValues[$valueKey])) {
            return;
        }

        $seenLabels[$labelKey] = true;
        $seenValues[$valueKey] = true;

        $rows[] = [
            'label' => $label,
            'value' => $value,
        ];
    }

    protected function resolveValue(array $data, string $key): ?string
    {
        if ($key === 'emails') {
            return $data['emails'][0]['value'] ?? null;
        }

        if ($key === 'contact_numbers') {
            return $data['contact_numbers'][0]['value'] ?? null;
        }

        if (! isset($data[$key])) {
            return null;
        }

        return $this->formatDisplayValue($data[$key]);
    }

    protected function formatDisplayValue(mixed $value): ?string
    {
        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_scalar($value)) {
            $formatted = trim((string) $value);

            if ($formatted === '' || $formatted === '__other__') {
                return null;
            }

            return $formatted;
        }

        if (! is_array($value) || $value === []) {
            return null;
        }

        // Portal phone object: { e164Number, number, dialCode, ... }
        if (isset($value['e164Number']) || isset($value['number']) || isset($value['internationalNumber'])) {
            foreach (['e164Number', 'internationalNumber', 'number'] as $phoneKey) {
                if (! empty($value[$phoneKey]) && is_scalar($value[$phoneKey])) {
                    $phone = trim((string) $value[$phoneKey]);

                    if ($phone !== '') {
                        return $phone;
                    }
                }
            }
        }

        // CRM-style contact arrays: [{value, label}, ...]
        if (isset($value[0]) && is_array($value[0]) && array_key_exists('value', $value[0])) {
            $parts = collect($value)
                ->map(fn ($item) => is_array($item) ? trim((string) ($item['value'] ?? '')) : '')
                ->filter(fn ($item) => $item !== '')
                ->values()
                ->all();

            return $parts === [] ? null : implode(', ', $parts);
        }

        // Q&A style nested arrays
        if (isset($value[0]) && is_array($value[0]) && isset($value[0]['question'], $value[0]['answer'])) {
            $parts = collect($value)
                ->map(function ($item) {
                    if (! is_array($item) || ! isset($item['question'], $item['answer'])) {
                        return null;
                    }

                    $answer = $this->formatDisplayValue($item['answer']);

                    return $answer === null ? null : trim((string) $item['question']).': '.$answer;
                })
                ->filter()
                ->values()
                ->all();

            return $parts === [] ? null : implode("\n", $parts);
        }

        // Skip associative objects (timestamps, nested maps) — only render simple lists.
        if (! array_is_list($value)) {
            return null;
        }

        $parts = collect($value)
            ->map(function ($item) {
                if (is_scalar($item)) {
                    return trim((string) $item);
                }

                if (is_array($item)) {
                    return $this->formatDisplayValue($item) ?? '';
                }

                return '';
            })
            ->filter(fn ($item) => $item !== '')
            ->values()
            ->all();

        return $parts === [] ? null : implode(', ', $parts);
    }

    protected function humanizeKey(string $key): string
    {
        if (str_contains($key, ' ')) {
            return $key;
        }

        return Str::headline($key);
    }
}
