<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Avisa al navegador cuándo terminó de generarse un archivo descargable (Excel, PDF...).
 *
 * El script public/assets/conbienes/descargas.js envía un parámetro "descarga_token" con cada exportación
 * y muestra "Generando informe…". Si la respuesta es un archivo, aquí se devuelve la cookie
 * "descarga_token" con el mismo valor; el script la detecta y cambia el aviso a "¡Listo!".
 */
class MarcarDescarga
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $token = (string) $request->input('descarga_token', '');
        if ($token === '' || ! preg_match('/^[A-Za-z0-9]{8,40}$/', $token)) {
            return $response;
        }

        $esArchivo = $response instanceof BinaryFileResponse
            || $response instanceof StreamedResponse
            || str_contains((string) $response->headers->get('Content-Disposition'), 'attachment');

        if ($esArchivo) {
            // Cookie legible por JavaScript (no lleva datos sensibles: solo el token aleatorio del aviso)
            $response->headers->setCookie(
                Cookie::create('descarga_token', $token, now()->addMinutes(5), '/', null, $request->isSecure(), false, false, Cookie::SAMESITE_LAX)
            );
        }

        return $response;
    }
}
