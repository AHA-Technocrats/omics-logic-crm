<?php

namespace AHATechnocrats\WebForm\Listeners;

use AHATechnocrats\WebForm\Services\WebFormShortUrl;

class CreateWebFormShortUrl
{
    public function __construct(
        protected WebFormShortUrl $shortUrl,
    ) {}

    public function handle($webForm): void
    {
        $this->shortUrl->ensureFor($webForm);
    }
}
