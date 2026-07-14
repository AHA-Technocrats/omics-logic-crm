<?php

namespace AHATechnocrats\WebForm\Services;

use AHATechnocrats\WebForm\Models\WebForm;
use AshAllenDesign\ShortURL\Classes\Builder;
use AshAllenDesign\ShortURL\Models\ShortURL;
use Illuminate\Support\Str;

class WebFormShortUrl
{
    public function destinationUrl(WebForm $webForm): string
    {
        return route('admin.settings.web_forms.preview', $webForm->form_id);
    }

    public function ensureFor(WebForm $webForm): ShortURL
    {
        if (filled($webForm->short_url_key)) {
            $existing = ShortURL::findByKey($webForm->short_url_key);

            if ($existing) {
                return $existing;
            }
        }

        $destination = $this->destinationUrl($webForm);

        $existingByDestination = ShortURL::findByDestinationURL($destination)->first();

        if ($existingByDestination) {
            $this->rememberKey($webForm, $existingByDestination->url_key);

            return $existingByDestination;
        }

        $preferredKey = $this->preferredKey($webForm);

        try {
            $shortUrl = app(Builder::class)
                ->destinationUrl($destination)
                ->urlKey($preferredKey)
                ->secure(false)
                ->trackVisits()
                ->make();
        } catch (\Throwable) {
            $shortUrl = app(Builder::class)
                ->destinationUrl($destination)
                ->secure(false)
                ->trackVisits()
                ->make();
        }

        $this->rememberKey($webForm, $shortUrl->url_key);

        return $shortUrl;
    }

    public function publicUrl(WebForm $webForm): string
    {
        $shortUrl = $this->ensureFor($webForm);

        return route('short-url.invoke', ['shortURLKey' => $shortUrl->url_key]);
    }

    protected function preferredKey(WebForm $webForm): string
    {
        $fromFormId = Str::lower(substr(preg_replace('/[^a-zA-Z0-9]/', '', (string) $webForm->form_id) ?: '', 0, 8));

        if (strlen($fromFormId) >= 5) {
            return $fromFormId;
        }

        return Str::lower(Str::random(8));
    }

    protected function rememberKey(WebForm $webForm, string $urlKey): void
    {
        if ($webForm->short_url_key === $urlKey) {
            return;
        }

        $webForm->forceFill(['short_url_key' => $urlKey])->saveQuietly();
    }
}
