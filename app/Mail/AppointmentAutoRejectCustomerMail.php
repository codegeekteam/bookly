<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentAutoRejectCustomerMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct( public Appointment $appointment)
    {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            // subject: 'Appointment cancelled - تم رفض الموعد',
             subject: 'Appointment cancelled'
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $serviceDate = $this->appointment->services[0]->pivot->date ?? now();       
        return new Content(
            view: 'emails.appointment-auto-reject-customer',
              with: [
                        'serviceDate' => $serviceDate
                    ]
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
