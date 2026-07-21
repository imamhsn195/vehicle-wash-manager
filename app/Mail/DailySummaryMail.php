<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailySummaryMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $summary
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Daily Wash Summary — '.$this->summary['date'],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.daily-summary',
        );
    }
}
