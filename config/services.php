<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
     'azure' => [
        'tenant_id' => env('AZURE_TENANT_ID'),
        'client_id' => env('AZURE_CLIENT_ID'),
        'client_secret' => env('AZURE_CLIENT_SECRET'),
        'authority' => env('AZURE_AUTHORITY'),
        'scope' => env('AZURE_SCOPE'),
    ],
    'sharepoint' => [
        'site_name' => env('SHAREPOINT_SITE_NAME'),
        'domain' => env('SHAREPOINT_DOMAIN'),
    ],
    'microsoft' => [
        'client_id' => env('MICROSOFT_CLIENT_ID'),
        'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
        'redirect' => env('MICROSOFT_REDIRECT_URI'),
        'tenant' => env('MICROSOFT_TENANT_ID'),
    ],
    'bc' => [
        'company_id' => env('BC_COMPANY_ID'),
        'environment' => env('BC_ENVIRONMENT', 'Production'),

        // true: los envíos a BC se encolan (requiere el worker/scheduler corriendo).
        // false: se ejecutan en la misma petición (comportamiento actual).
        'async' => (bool) env('BC_ASYNC', false),
        'dim_tercero' => env('BC_DIM_TERCERO', 'TERCERO'),

        // Parámetros contables para el envío de facturas de Bono Regalo (no se aceptan desde el request)
        'bono_regalo' => [
            'tipo_documento' => env('BC_BR_TIPO_DOCUMENTO', 'Invoice'),
            'cta_detalle' => env('BC_BR_CTA_DETALLE', '11051002'),
            'cta_total' => env('BC_BR_CTA_TOTAL', 'BANCOLOMBIA 6289 BR'),
            'libro_codigo' => env('BC_BR_LIBRO_CODIGO', '1_NIIF'),
            'codigo_concepto' => env('BC_BR_CODIGO_CONCEPTO', '110409'),
            'grupo_impuesto' => env('BC_BR_GRUPO_IMPUESTO', 'G COMPR 0%_'),
            'area_impuesto' => env('BC_BR_AREA_IMPUESTO', 'CLI-NRI'),
            'diario' => env('BC_BR_DIARIO', 'BONOREGALO'),
            'seccion' => env('BC_BR_SECCION', 'BONO REG'),
        ],
    ],

];
