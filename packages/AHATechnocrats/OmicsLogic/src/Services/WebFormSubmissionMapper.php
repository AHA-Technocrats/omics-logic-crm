<?php

namespace AHATechnocrats\OmicsLogic\Services;

use AHATechnocrats\Lead\Repositories\SourceRepository;
use AHATechnocrats\Product\Models\Product;
use AHATechnocrats\WebForm\Models\WebForm;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class WebFormSubmissionMapper
{
    public function __construct(protected SourceRepository $sourceRepository) {}

    public function map(array $input, ?WebForm $webForm = null): array
    {
        $personInput = Arr::wrap($input['persons'] ?? []);
        $leadInput = Arr::wrap($input['leads'] ?? []);

        $email = $this->value($input, $personInput, [
            'email',
            'email_address',
            'email address',
            'persons.emails.0.value',
        ]) ?? $this->nestedEmail($personInput);

        $name = $this->value($input, $personInput, [
            'name',
            'full_name',
            'full name',
            'full name:',
        ]);

        $phone = $this->value($input, $personInput, [
            'phone',
            'phone_number',
            'phone number',
            'phone number:',
            'contact_number',
            'persons.contact_numbers.0.value',
        ]) ?? $this->nestedPhone($personInput);

        $country = $this->value($input, $personInput, [
            'country',
            'country:',
            'country_code',
            'persons.country_code',
            'organizations.country_code',
        ]);

        $organizationName = $this->value($input, $personInput, [
            'organization_name',
            'organization',
            'company',
            'company/organization/university',
            'company/organization/university:',
            'university',
            'persons.organization_name',
            'organizations.name',
        ]);

        $education = $this->value($input, $personInput, [
            'education_level',
            'education',
            'level of education',
            'level of education:',
        ]);

        $inquiryDetails = $this->value($input, $personInput, [
            'inquiry_details',
            'other_details',
            'other details',
            'any other details/queries you wish to mention',
            'any other details/queries you wish to mention:',
            'queries',
            'notes',
        ]);

        $programInterest = $this->arrayOrStringValue($input, $personInput, [
            'program_interest',
            'interested_in_program',
            'interested in program',
            'program',
            'persons.program_interest',
        ]);

        $programInterestOther = $this->value($input, $personInput, [
            'program_interest_other',
            'program_other',
        ]);

        if (is_array($programInterest)) {
            $firstProgram = $programInterest[0] ?? null;
            $programInterestStr = implode(', ', $programInterest);
        } else {
            $firstProgram = $programInterest;
            $programInterestStr = $programInterest;
        }

        if ($programInterestStr === '__other__' || str_starts_with(strtolower(trim((string) $programInterestStr)), 'other')) {
            $programInterestStr = $programInterestOther ? trim($programInterestOther) : null;
            $firstProgram = $programInterestStr;
        }

        $submittedAt = $this->value($input, $personInput, [
            'timestamp',
            'submitted_at',
            'submitted at',
        ]);

        $webFormSourceId = $this->resolveWebFormSourceId();
        $primaryProductId = $this->resolveProductId($firstProgram) ?? $webForm?->product_id;

        $person = array_filter([
            'name' => $name,
            'emails' => $email ? [['value' => $email, 'label' => 'work']] : null,
            'contact_numbers' => $phone ? [['value' => $phone, 'label' => 'work']] : null,
            'organization_name' => $organizationName,
            'country_code' => $country,
            'education_level' => $this->normalizeEducation($education),
            'inquiry_details' => $this->normalizeInquiryDetails($inquiryDetails),
            'program_interest' => $programInterestStr,
            'primary_product_id' => $primaryProductId,
            'primary_source_id' => $webFormSourceId,
            'entity_type' => 'persons',
        ], fn ($value) => $value !== null && $value !== '');

        $person = array_merge($personInput, $person);

        if ($email) {
            $person['emails'] = [['value' => $email, 'label' => 'work']];
        }

        if ($phone) {
            $person['contact_numbers'] = [['value' => $phone, 'label' => 'work']];
        }

        $leadTitle = $leadInput['title'] ?? ($name ? "Web Form — {$name}" : 'Web Form Lead');
        $leadDescription = $leadInput['description'] ?? null;

        return [
            'person' => $person,
            'organization' => array_filter([
                'name' => $organizationName,
                'country_code' => $country,
            ]),
            'lead' => array_filter([
                'title' => $leadTitle,
                'description' => $leadDescription,
            ]),
            'submitted_at' => $this->parseTimestamp($submittedAt),
        ];
    }

    public function normalizeEducation(?string $value): ?string
    {
        if ($value === null || trim($value) === '' || strtolower(trim($value)) === 'none') {
            return null;
        }

        $normalized = strtolower(trim($value));

        return match (true) {
            str_contains($normalized, 'doctor') || str_contains($normalized, 'ph.d') || $normalized === 'phd' => 'PhD',
            str_contains($normalized, 'master') => 'Masters',
            str_contains($normalized, 'undergrad') || str_contains($normalized, 'bachelor') => 'Undergraduate',
            str_contains($normalized, 'faculty') => 'Faculty',
            str_contains($normalized, 'industry') => 'Industry',
            in_array($value, ['Undergraduate', 'Masters', 'PhD', 'Faculty', 'Industry'], true) => $value,
            default => $value,
        };
    }

    protected function normalizeInquiryDetails(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if ($value === '' || strtolower($value) === 'none') {
            return null;
        }

        return $value;
    }

    protected function resolveWebFormSourceId(): ?int
    {
        $source = $this->sourceRepository->findOneByField('name', 'Web Form')
            ?: $this->sourceRepository->first();

        return $source?->id;
    }

    protected function resolveProductId(?string $programName): ?int
    {
        if (! $programName || trim($programName) === '') {
            return null;
        }

        $programName = trim($programName);

        $product = Product::query()->where('name', $programName)->first();

        if ($product) {
            return $product->id;
        }

        $aliasMatch = DB::table('omics_product_aliases')
            ->where('alias_name', $programName)
            ->first();

        return $aliasMatch?->product_id;
    }

    protected function parseTimestamp(?string $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function nestedEmail(array $personInput): ?string
    {
        return $personInput['emails'][0]['value'] ?? null;
    }

    protected function nestedPhone(array $personInput): ?string
    {
        return $personInput['contact_numbers'][0]['value'] ?? null;
    }

    protected function value(array $input, array $personInput, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (isset($personInput[$key]) && is_scalar($personInput[$key]) && trim((string) $personInput[$key]) !== '') {
                return trim((string) $personInput[$key]);
            }

            if (isset($input[$key]) && is_scalar($input[$key]) && trim((string) $input[$key]) !== '') {
                return trim((string) $input[$key]);
            }
        }

        return null;
    }

    protected function arrayOrStringValue(array $input, array $personInput, array $keys)
    {
        foreach ($keys as $key) {
            if (isset($personInput[$key])) {
                $val = $personInput[$key];
                if (is_array($val) && ! empty($val)) {
                    return $val;
                }
                if (is_scalar($val) && trim((string) $val) !== '') {
                    return trim((string) $val);
                }
            }

            if (isset($input[$key])) {
                $val = $input[$key];
                if (is_array($val) && ! empty($val)) {
                    return $val;
                }
                if (is_scalar($val) && trim((string) $val) !== '') {
                    return trim((string) $val);
                }
            }
        }

        return null;
    }
}
