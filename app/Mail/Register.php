<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Register extends Mailable
{
    use Queueable, SerializesModels;

    public $datamail;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($datamail)
    {
        $this->datamail = $datamail;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.register')
                    ->from('noreply@ts3.co.id', 'Admin IT TS3')
                    ->subject('Register Akun TS3')
                    ->with([
                        'username' => $this->datamail['username'],
                        'fullname' => $this->datamail['fullname'],
                        'email' => $this->datamail['email'],
                        'url' => $this->datamail['url']
                    ]);
    }
}