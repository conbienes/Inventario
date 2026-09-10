<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProdAprob extends Mailable
{
    use Queueable, SerializesModels;

    public $Correo;
    public $Empleado;

    public $subject="CONBIENES: Cantidad Entregada";
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($Correo,$Empleado)
    {
        $this->Correo =$Correo;
        $this->Empleado=$Empleado;       
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('Emails.AprobPedido');
    }
}
