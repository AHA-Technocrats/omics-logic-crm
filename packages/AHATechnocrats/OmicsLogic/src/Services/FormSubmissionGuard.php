<?php

namespace AHATechnocrats\OmicsLogic\Services;

use AHATechnocrats\Contact\Models\Person;
use AHATechnocrats\OmicsLogic\Models\DisposableEmailDomain;
use AHATechnocrats\WebForm\Models\WebForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class FormSubmissionGuard
{
    public function __construct(
        protected OrganizationNormalizer $organizationNormalizer,
    ) {}

    /**
     * @return array{spam_score: int, warnings: array<int, string>}
     */
    public function validate(Request $request, WebForm $webForm): array
    {
        $warnings = [];
        $spamScore = 0;

        if ($webForm->honeypot_enabled) {
            $honeypotRejectMessage = config(
                'omicslogic.anti_spam.honeypot_reject_message',
                'Do not fill the data in the person and organisation.'
            );

            $honeypotField = config('omicslogic.anti_spam.honeypot_field', '_website_url');
            $personHoneypot = config('omicslogic.anti_spam.honeypot_person_field', 'persons_hp.name');
            $organizationHoneypot = config('omicslogic.anti_spam.honeypot_organization_field', 'organizations_hp.name');

            if (
                $request->filled($honeypotField)
                || $request->filled($personHoneypot)
                || $request->filled($organizationHoneypot)
            ) {
                throw ValidationException::withMessages([
                    'form' => [$honeypotRejectMessage],
                ]);
            }
        }

        if ($webForm->min_submit_seconds) {
            $token = $request->input('_form_token');
            $renderedAt = $token ? Cache::get('form_token:'.$token) : null;

            if (! $renderedAt || (time() - (int) $renderedAt) < $webForm->min_submit_seconds) {
                $spamScore += 40;
                $warnings[] = 'submitted_too_fast';
            }
        }

        $this->enforceRateLimits($request, $webForm);

        if ($webForm->turnstile_enabled && config('omicslogic.turnstile.secret_key')) {
            $this->verifyTurnstile($request);
        }

        $email = $this->extractPrimaryEmail($request);

        if ($email && $webForm->block_disposable) {
            $domain = strtolower(substr(strrchr($email, '@'), 1) ?: '');

            if ($domain && DisposableEmailDomain::query()->where('domain', $domain)->exists()) {
                throw ValidationException::withMessages([
                    'persons.emails.0.value' => ['Disposable email addresses are not allowed.'],
                ]);
            }
        }

        if ($email && $this->isDuplicateEmail($email)) {
            $spamScore += 15;
            $warnings[] = 'duplicate_email';
        }

        $phone = $this->extractPrimaryPhone($request);

        if ($phone && $this->isDuplicatePhone($phone)) {
            $spamScore += 10;
            $warnings[] = 'duplicate_phone';
        }

        return [
            'spam_score' => min(100, $spamScore),
            'warnings' => $warnings,
        ];
    }

    public function issueFormToken(): string
    {
        $token = bin2hex(random_bytes(16));
        Cache::put('form_token:'.$token, time(), now()->addHour());

        return $token;
    }

    protected function enforceRateLimits(Request $request, WebForm $webForm): void
    {
        $ip = $request->ip() ?? 'unknown';
        $ipKey = 'form_submit_ip:'.$webForm->id.':'.$ip;
        $ipLimit = $webForm->rate_limit_per_ip ?: config('omicslogic.anti_spam.rate_limit_ip', 10);

        if (RateLimiter::tooManyAttempts($ipKey, $ipLimit)) {
            throw ValidationException::withMessages([
                'form' => ['Too many submissions. Please try again later.'],
            ]);
        }

        RateLimiter::hit($ipKey, 3600);

        $email = $this->extractPrimaryEmail($request);

        if ($email) {
            $emailKey = 'form_submit_email:'.$webForm->id.':'.md5(strtolower($email));
            $emailLimit = $webForm->rate_limit_per_email ?: config('omicslogic.anti_spam.rate_limit_email', 5);

            if (RateLimiter::tooManyAttempts($emailKey, $emailLimit)) {
                throw ValidationException::withMessages([
                    'persons.emails.0.value' => ['This email has submitted too many times. Please try again later.'],
                ]);
            }

            RateLimiter::hit($emailKey, 3600);
        }
    }

    protected function verifyTurnstile(Request $request): void
    {
        $response = $request->input('cf-turnstile-response');

        if (! $response) {
            throw ValidationException::withMessages([
                'form' => ['Please complete the security check.'],
            ]);
        }

        $verify = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => config('omicslogic.turnstile.secret_key'),
            'response' => $response,
            'remoteip' => $request->ip(),
        ]);

        if (! $verify->ok() || ! $verify->json('success')) {
            throw ValidationException::withMessages([
                'form' => ['Security verification failed. Please try again.'],
            ]);
        }
    }

    protected function extractPrimaryEmail(Request $request): ?string
    {
        $email = $request->input('persons.emails.0.value');

        return $email ? strtolower(trim($email)) : null;
    }

    protected function extractPrimaryPhone(Request $request): ?string
    {
        $phone = $request->input('persons.contact_numbers.0.value');

        return $phone ? preg_replace('/\D+/', '', $phone) : null;
    }

    protected function isDuplicateEmail(string $email): bool
    {
        return Person::query()
            ->where('normalized_email', $email)
            ->exists();
    }

    protected function isDuplicatePhone(string $phone): bool
    {
        return Person::query()
            ->where('normalized_phone', $phone)
            ->exists();
    }
}
