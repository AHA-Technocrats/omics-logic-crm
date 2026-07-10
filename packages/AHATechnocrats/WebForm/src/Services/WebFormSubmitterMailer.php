<?php

namespace AHATechnocrats\WebForm\Services;

use AHATechnocrats\Core\Services\SafeMailDispatcher;
use AHATechnocrats\EmailTemplate\Models\EmailTemplate;
use AHATechnocrats\WebForm\Contracts\WebForm as WebFormContract;
use AHATechnocrats\WebForm\Mail\WebFormSubmitterMail;
use Illuminate\Support\Arr;

class WebFormSubmitterMailer
{
    public function __construct(protected SafeMailDispatcher $safeMailDispatcher) {}

    public function sendIfConfigured(WebFormContract $webForm, array $submissionPayload): void
    {
        if (! $webForm->send_submitter_email || ! $webForm->email_template_id) {
            return;
        }

        $email = $this->resolveSubmitterEmail($submissionPayload);

        if (! $email) {
            return;
        }

        $template = EmailTemplate::query()->find($webForm->email_template_id);

        if (! $template) {
            return;
        }

        $replacements = $this->buildReplacements($submissionPayload, $webForm);

        $this->safeMailDispatcher->dispatch(
            new WebFormSubmitterMail($template, $replacements),
            $email,
        );
    }

    protected function resolveSubmitterEmail(array $payload): ?string
    {
        $person = Arr::wrap($payload['persons'] ?? []);

        $email = $person['emails'][0]['value'] ?? $person['email'] ?? null;

        if (! is_string($email) || trim($email) === '') {
            return null;
        }

        return trim($email);
    }

    /**
     * @return array<string, string>
     */
    protected function buildReplacements(array $payload, WebFormContract $webForm): array
    {
        $person = Arr::wrap($payload['persons'] ?? []);

        $replacements = [
            'name' => (string) ($person['name'] ?? ''),
            'email' => (string) ($person['emails'][0]['value'] ?? $person['email'] ?? ''),
            'organization' => (string) ($person['organization_name'] ?? $person['organization'] ?? ''),
            'program' => (string) (($person['program_interest'] ?? '') === '__other__'
              ? ($person['program_interest_other'] ?? '')
              : ($person['program_interest'] ?? '')),
            'phone' => (string) ($person['contact_numbers'][0]['value'] ?? $person['phone'] ?? ''),
            'country' => (string) ($person['country_code'] ?? $person['country'] ?? ''),
            'education' => (string) ($person['education_level'] ?? $person['education'] ?? ''),
            'inquiry_details' => (string) ($person['inquiry_details'] ?? $person['queries'] ?? ''),
            'form_title' => (string) $webForm->title,
        ];

        foreach ($person as $key => $value) {
            if (is_scalar($value)) {
                $replacements[(string) $key] = (string) $value;
            }
        }

        foreach ($webForm->attributes()->with('attribute')->get() as $webFormAttribute) {
            $code = $webFormAttribute->attribute?->code;

            if (! $code) {
                continue;
            }

            $value = $person[$code] ?? null;

            if (is_scalar($value)) {
                $replacements[$code] = (string) $value;
            }
        }

        return $replacements;
    }
}
