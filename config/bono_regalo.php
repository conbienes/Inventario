<?php

/*
| Configuración del módulo Bono Regalo.
| Centraliza datos que antes estaban repetidos en controladores, vistas y el correo.
*/

return [

    // id del módulo en la tabla de módulos / empleado_modulo_permiso
    'modulo_id' => 3,

    // Permisos del módulo con acceso administrativo (ver e imprimir ventas de todos)
    // 1 = Administrador, 2 = Administrador2
    'permisos_admin' => [1, 2],

    // Datos de la empresa para recibos impresos y correos
    'empresa' => [
        'nombre' => env('BR_EMPRESA_NOMBRE', 'ADMINISTRACION MAYORCA S.A.S'),
        'nit' => env('BR_EMPRESA_NIT', '901344877-7'),
        'direccion' => env('BR_EMPRESA_DIRECCION', 'CL 51 SUR 48 57 ET1 P8'),
        'telefono' => env('BR_EMPRESA_TELEFONO', '6042333'),
    ],

    'tipos_documento' => [
        'Cédula de Ciudadanía',
        'Registro Civil',
        'Tarjeta de Identidad',
        'Tarjeta de Extranjería',
        'NIT',
        'Pasaporte',
        'NIT de otro país',
    ],

    // Tipos de documento que corresponden a empresa (razón social en vez de nombres)
    'documentos_empresa' => ['NIT', 'NIT de otro país'],

    'tipos_actividad' => ['CLI-NRI', 'CLI-RI'],
];
