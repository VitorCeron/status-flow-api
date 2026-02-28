<?php

namespace App\Mail;

use App\Models\Monitor;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MonitorDownMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param Monitor $monitor
     */
    public function __construct(
        public readonly Monitor $monitor,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[Alert] Monitor \"{$this->monitor->name}\" is DOWN",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.monitor-down',
        );
    }
}
