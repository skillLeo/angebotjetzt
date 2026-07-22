<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

abstract class BaseBrandMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    abstract protected function subjectLine(): string;

    abstract protected function title(): string;

    /** @return array<int, string> */
    abstract protected function lines(): array;

    /** @return array{url: string, label: string}|null */
    protected function cta(): ?array
    {
        return null;
    }

    protected function footnote(): ?string
    {
        return null;
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine());
    }

    public function content(): Content
    {
        return new Content(view: 'emails.standard', with: array_filter([
            'subject' => $this->subjectLine(),
            'title' => $this->title(),
            'lines' => $this->lines(),
            'cta' => $this->cta(),
            'footnote' => $this->footnote(),
        ]));
    }
}
