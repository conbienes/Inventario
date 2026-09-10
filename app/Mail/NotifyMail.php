<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotifyMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pedidoinventario;
    public $Empleado;
    public $Area;
    public $FechaT;
    public $Observacion;

    public $subject="CONBIENES";
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($pedidoinventario,$Empleado,$Area,$FechaT,$Observacion)
    {
        $this->pedidoinventario =$pedidoinventario;
        $this->Empleado=$Empleado;
        $this->Area=$Area;
        $this->FechaT=$FechaT;
        $this->Observacion=$Observacion;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('Emails.Inventario');
    }
}
