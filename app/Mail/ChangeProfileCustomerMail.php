<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ChangeProfileCustomerMail extends Mailable
{
    use Queueable, SerializesModels;
    public $customer;
    public $temp_customer;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($customer, $temp_customer)
    {
        $this->customer = $customer;
        $this->temp_customer = $temp_customer;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            from: env('MAIL_FROM_ADDRESS'),
            subject: 'Kunden-Profil Änderung im Bestellportal | ' . $this->customer->customer_number,
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
            html: 'email.change-profile-customer',
            with: [
                'customer' => $this->temp_customer,
                'temp_customer' => $this->customer->id,
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
