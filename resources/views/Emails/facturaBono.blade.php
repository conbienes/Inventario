<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Recibo {{ $factura->numero_factura }}</title>
</head>

<body>
    <!-- Imagen enlazada -->
    <a href="https://mayorca.com.co/bono-regalo-mayorca/?utm_source=email&utm_medium=email&utm_campaign=bono-regalo&utm_content=home-bono-regalo" target="_blank">
        <img src="{{ $message->embed(public_path('assets/lte/dist/img/header.png')) }}" alt="Encabezado"
            style="max-width:100%; height:auto; display:block; margin-bottom:16px; border:0;">
    </a>
</body>
</html>
