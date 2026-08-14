<?php

namespace AHATechnocrats\WebForm\Mail;

use AHATechnocrats\Automation\Helpers\Entity\Lead as LeadPlaceholders;
use AHATechnocrats\Automation\Helpers\Entity\Person as PersonPlaceholders;
use AHATechnocrats\Contact\Contracts\Person;
use AHATechnocrats\EmailTemplate\Models\EmailTemplate;
use AHATechnocrats\Lead\Contracts\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WebFormSubmitterMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, string>  $replacements
     */
    public function __construct(
        public EmailTemplate $template,
        public array $replacements,
        public ?Person $person = null,
        public ?Lead $lead = null,
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
            foreach (['{{'.$key.'}}', '{{ '.$key.' }}', '{%persons.'.$key.'%}', '{% persons.'.$key.' %}'] as $token) {
                $search[] = $token;
                $replace[] = $value;
            }
        }

        $content = str_replace($search, $replace, $content);

        /**
         * The template editor's "Placeholders" dropdown emits {%persons.code%} /
         * {%leads.code%} for every attribute, which the submitted payload alone
         * cannot cover. Resolve those against the saved records.
         */
        if ($this->lead) {
            $content = app(LeadPlaceholders::class)->replacePlaceholders($this->lead, $content);
        } elseif ($this->person) {
            $content = app(PersonPlaceholders::class)->replacePlaceholders($this->person, $content);
        }

        return $this->stripUnresolved($content);
    }

    /**
     * A placeholder with no value must render as empty, never as raw text.
     */
    protected function stripUnresolved(string $content): string
    {
        return preg_replace(
            ['/\{%\s*[\w.]+\s*%\}/', '/\{\{\s*[\w.]+\s*\}\}/'],
            '',
            $content
        ) ?? $content;
    }
}
