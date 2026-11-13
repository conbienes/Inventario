<?php

return [

    /*
     * Correos que reciben la notificación de cada nueva solicitud de productos.
     * Se configuran en .env con INVENTARIO_CORREOS_PEDIDOS (separados por coma).
     */
    'correos_pedidos' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('INVENTARIO_CORREOS_PEDIDOS', 'alexanderq@conbienes.com'))
    ))),

];
