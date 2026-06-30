<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ServiceProviderApprovedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $serviceProvider;
    public $flag;
    /**
     * Create a new message instance.
     */
    public function __construct($serviceProvider, $flag)
    {
        $this->serviceProvider = $serviceProvider;
        $this->flag = $flag;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Service Provider Activate/Deactivate Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.provider-approved',
            with: ['serviceProvider' => $this->serviceProvider, 'flag' => $this->flag]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
