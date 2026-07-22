<?php

namespace App\Mail;

class ContactFormMail extends BaseBrandMail
{
    /** @param array{name: string, email: string, subject: string, message: string} $data */
    public function __construct(public array $data)
    {
    }

    protected function subjectLine(): string
    {
        return 'Kontaktanfrage: '.$this->data['subject'];
    }

    protected function title(): string
    {
        return 'Neue Kontaktanfrage';
    }

    protected function lines(): array
    {
        return [
            "Von: <strong>{$this->data['name']}</strong> ({$this->data['email']})",
            "Betreff: {$this->data['subject']}",
            nl2br(e($this->data['message'])),
        ];
    }
}
