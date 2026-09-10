<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotiEmpleado extends Mailable
{
    use Queueable, SerializesModels;

    public $pedidoinventario;

    public $subject="CONBIENES: Solicitud Enviada";
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($pedidoinventario)
    {
        $this->pedidoinventario =$pedidoinventario;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('Emails.NotiEmpleado');
    }
}
