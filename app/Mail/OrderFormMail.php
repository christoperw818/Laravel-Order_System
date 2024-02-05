<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class OrderFormMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $order;
    public $customer;
    public $em_parameter;
    public $ve_parameter;
    public $files;


    public function __construct($order, $customer, $em_parameter, $ve_parameter, $files)
    {
        $this->order = $order;
        $this->customer = $customer;
        $this->em_parameter = $em_parameter;
        $this->ve_parameter = $ve_parameter;
        $this->files = $files;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */

    public function envelope()
    {
        $subject = $this->order->type == 'Embroidery' ? 'Neue Bestellung Stickprogramm | ' : 'Neue Bestellung Vektordatei | ';
        $subject .= $this->customer->customer_number . '-' . $this->order->order_number;

        return new Envelope(
            from: env('MAIL_FROM_ADDRESS'),
            subject: $subject,
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
            html: 'email.order_form',
            with: [
                'order' => $this->order,
                'customer' => $this->customer,
                'em_parameter' => $this->em_parameter,
                've_parameter' => $this->ve_parameter,
                'files' => $this->files,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        $attachments = [];
        foreach ($this->files as $file) {
            $attachments[] = Attachment::fromStorage($file);
        }
        return $attachments;
    }
}
