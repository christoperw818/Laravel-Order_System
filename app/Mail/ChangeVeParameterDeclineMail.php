<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ChangeVeParameterDeclineMail extends Mailable
{
    use Queueable, SerializesModels;
    public $customer;
    public $parameter;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($customer, $parameter)
    {
        $this->customer = $customer;
        $this->parameter = $parameter;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            from: "info@lionwerbung.de",
            subject: 'Decline Change Vector Parameter',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            html: 'email.change-ve-parameter-decline',
            with: [
                'customer' => $this->customer,
                'parameter' => $this->parameter,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}