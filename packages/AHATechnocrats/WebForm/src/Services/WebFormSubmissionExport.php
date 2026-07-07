<?php

namespace AHATechnocrats\WebForm\Services;

use AHATechnocrats\WebForm\Contracts\WebForm as WebFormContract;
use AHATechnocrats\WebForm\Helpers\WebFormFieldOrder;
use AHATechnocrats\WebForm\Models\WebFormSubmission;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class WebFormSubmissionExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    /**
     * @var list<string>
     */
    protected array $headings = [];

    public function __construct(
        protected WebFormContract $webForm,
        protected Collection $submissions,
    ) {
        $this->headings = $this->buildHeadings();
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function collection(): Collection
    {
        return $this->submissions->map(fn (WebFormSubmission $submission) => $this->mapRow($submission));
    }

    /**
     * @return list<string>
     */
    protected function buildHeadings(): array
    {
        $headings = ['Timestamp', 'Status'];

        foreach ($this->columnDefinitions() as $column) {
            $headings[] = $column['label'];
        }

        return $headings;
    }

    /**
     * @return list<string|int|float|null>
     */
    protected function mapRow(WebFormSubmission $submission): array
    {
        $personData = Arr::wrap($submission->payload['persons'] ?? []);
        $row = [
            $submission->created_at?->format('Y-m-d H:i:s'),
            $submission->status,
        ];

        foreach ($this->columnDefinitions() as $column) {
            $row[] = $this->resolveColumnValue($personData, $submission, $column);
        }

        return $row;
    }

    /**
     * @return list<array{key: string, label: string, type: string}>
     */
    protected function columnDefinitions(): array
    {
        $columns = [];
        $builtinLabels = [
            'builtin:name' => 'Full Name',
            'builtin:email' => 'Email Address',
            'builtin:organization' => 'Organization',
            'builtin:phone' => 'Phone number',
            'builtin:country' => 'Country',
            'builtin:education' => 'Level of Education',
            'builtin:program' => 'Interested in Campaign',
            WebFormFieldOrder::INQUIRY_DETAILS => 'Other details / queries',
        ];

        foreach (WebFormFieldOrder::resolveOrder($this->webForm) as $key) {
            if (isset($builtinLabels[$key])) {
                $columns[] = [
                    'key' => $key,
                    'label' => $builtinLabels[$key],
                    'type' => 'builtin',
                ];

                continue;
            }

            if (! str_starts_with($key, 'attribute:')) {
                continue;
            }

            $attributeId = (int) substr($key, strlen('attribute:'));
            $webFormAttribute = $this->webForm->attributes()->with('attribute')->find($attributeId);

            if (! $webFormAttribute) {
                continue;
            }

            $columns[] = [
                'key' => $key,
                'label' => $webFormAttribute->name,
                'type' => 'attribute',
                'code' => $webFormAttribute->attribute?->code,
            ];
        }

        return $columns;
    }

    /**
     * @param  array<string, mixed>  $personData
     * @param  array{key: string, label: string, type: string, code?: string|null}  $column
     */
    protected function resolveColumnValue(array $personData, WebFormSubmission $submission, array $column): ?string
    {
        if ($column['type'] === 'attribute') {
            $code = $column['code'] ?? null;

            if ($code && isset($personData[$code])) {
                return $this->stringify($personData[$code]);
            }

            return null;
        }

        return match ($column['key']) {
            'builtin:name' => $this->stringify($personData['name'] ?? null),
            'builtin:email' => $personData['emails'][0]['value'] ?? $this->stringify($personData['email'] ?? null),
            'builtin:organization' => $this->stringify($personData['organization_name'] ?? $personData['organization'] ?? null),
            'builtin:phone' => $personData['contact_numbers'][0]['value'] ?? $this->stringify($personData['phone'] ?? null),
            'builtin:country' => $this->stringify($personData['country_code'] ?? $personData['country'] ?? null),
            'builtin:education' => $this->stringify($personData['education_level'] ?? $personData['education'] ?? null),
            'builtin:program' => $this->stringify(
                ($personData['program_interest'] ?? '') === '__other__'
                  ? ($personData['program_interest_other'] ?? null)
                  : ($personData['program_interest'] ?? null)
            ),
            WebFormFieldOrder::INQUIRY_DETAILS => $this->stringify($personData['inquiry_details'] ?? $personData['queries'] ?? null),
            default => null,
        };
    }

    protected function stringify(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        return trim((string) $value);
    }
}
