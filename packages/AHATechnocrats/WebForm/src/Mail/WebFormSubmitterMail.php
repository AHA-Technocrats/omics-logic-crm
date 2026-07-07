<?php

namespace AHATechnocrats\WebForm\Mail;

use AHATechnocrats\EmailTemplate\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WebFormSubmitterMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, string>  $replacements
     */
    public function __construct(
        public EmailTemplate $template,
        public array $replacements,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->replacePlaceholders($this->template->subject),
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->replacePlaceholders($this->template->content),
        );
    }

    protected function replacePlaceholders(string $content): string
    {
        $search = [];
        $replace = [];

        foreach ($this->replacements as $key => $value) {
            $search[] = '{{'.$key.'}}';
            $search[] = '{{ '.$key.' }}';
            $replace[] = $value;
            $replace[] = $value;
        }

        return str_replace($search, $replace, $content);
    }
}
