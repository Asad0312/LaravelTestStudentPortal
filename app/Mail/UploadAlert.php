<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UploadAlert extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $subjectTitle,
        public string $body,
        public string $audience
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[' . config('app.name') . '] ' . $this->subjectTitle
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.upload-alert');
    }
}
