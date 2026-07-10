<?php

namespace AHATechnocrats\Core\Services;

use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

class SafeMailDispatcher
{
    /**
     * Queue or defer outbound mail so request flows (web forms, workflows) never fail on SMTP issues.
     */
    public function dispatch(Mailable $mailable, string|array|null $recipients = null): void
    {
        if ($this->usesSyncQueue()) {
            $this->dispatchAfterResponse($mailable, $recipients);

            return;
        }

        try {
            $this->pendingMail($recipients)->queue($mailable);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    protected function usesSyncQueue(): bool
    {
        return config('queue.default') === 'sync';
    }

    protected function dispatchAfterResponse(Mailable $mailable, string|array|null $recipients): void
    {
        dispatch(function () use ($mailable, $recipients) {
            try {
                $this->pendingMail($recipients)->send($mailable);
            } catch (\Throwable $exception) {
                report($exception);
            }
        })->afterResponse();
    }

    protected function pendingMail(string|array|null $recipients)
    {
        return $recipients === null ? Mail::mailer() : Mail::to($recipients);
    }
}
