<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConfirmationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $entity;

    public function __construct($entity)
    {
        $this->entity = $entity;
    }

    public function build()
    {
        return $this->view('app.email.account')
                    ->subject('Cuenta de acceso - '.env('APP_NAME'))
                    ->with([
                        'nombres' => $this->entity['nombres'],
                        'cuenta' => $this->entity['cuenta'],
                        'password' => $this->entity['password'],
                    ]);
    }
}