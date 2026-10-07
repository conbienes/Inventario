<?php

namespace App\Policies\BonoRegalo;

use App\Models\BonoRegalo\TarjetaBonoR;
use Illuminate\Auth\Access\Response;

/**
 * Reglas sobre tarjetas Bono Regalo.
 * El acceso al CRUD de tarjetas lo controla la ruta (nivel:3,1|2|5); aquí van las reglas del recurso:
 * una tarjeta vendida (con factura) no se puede editar, reactivar ni eliminar.
 */
class TarjetaBonoRPolicy
{
    public function update($user, TarjetaBonoR $tarjeta): Response
    {
        return $tarjeta->estaVendida()
            ? Response::deny("La tarjeta #{$tarjeta->numero} ya fue vendida y no se puede editar.")
            : Response::allow();
    }

    public function delete($user, TarjetaBonoR $tarjeta): Response
    {
        return $tarjeta->estaVendida()
            ? Response::deny("La tarjeta #{$tarjeta->numero} ya fue vendida y no se puede eliminar.")
            : Response::allow();
    }
}
