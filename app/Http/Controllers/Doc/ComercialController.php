<?php

namespace App\Http\Controllers\Doc;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use App\Services\AzureService;
use App\Models\documento;
use App\Models\InmuebleCliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class ComercialController extends Controller
{
    public function Editar(Request $request, $idInmueble)
    {
        try {
            // Validar que el idInmueble sea un número y exista en la tabla documentos
            if (!is_numeric($idInmueble) || !Documento::where('id_inmClient', $idInmueble)->exists()) {
                return redirect()->back()->with('error', 'El inmueble seleccionado no es válido.');
            }

            // Obtener documentos con filtros
            $query = Documento::where('id_inmClient', $idInmueble);

            if ($request->filled('nombre')) {
                $query->where('nombre', 'LIKE', '%' . $request->nombre . '%');
            }

            if ($request->filled('fecha_desde')) {
                $query->whereDate('created_at', '>=', $request->fecha_desde);
            }

            if ($request->filled('fecha_hasta')) {
                $query->whereDate('created_at', '<=', $request->fecha_hasta);
            }

            $documentos = $query->orderBy('fecha', 'desc')->paginate(15);

            // Obtener inmuebles relacionados
            $inmuebles = InmuebleCliente::select('id_inmueble_cliente', 'sharepoint_id')->where('sharepoint_id', $idInmueble)->distinct()->get();

            return view('Doc.Comercial.Edit', compact('documentos', 'inmuebles', 'idInmueble'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ocurrió un error inesperado. Inténtalo de nuevo.');
        }
    }

    public function EditarIndicidual(Request $request, $idInmueble, $doc)
    {
        try {
            // Buscar el documento o lanzar un error 404 si no existe
            $documento = Documento::where('id', $doc)->firstOrFail();

            $inmuebles = InmuebleCliente::select('id_inmueble_cliente', 'sharepoint_id')->where('sharepoint_id', $idInmueble)->distinct()->get();

            return view('Doc.Comercial.IndEdit', compact('documento', 'inmuebles'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'El inmueble o documento no existe.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ocurrió un error inesperado. Inténtalo de nuevo.');
        }
    }

    public function Inicio(Request $request)
    {
        try {
            // Verificar si el correo está en la sesión
            $email = session('user_email');

            // Si no hay correo en sesión, redirigir con un mensaje de error
            if (!$email) {
                return redirect()
                    ->route('files.index', ['idInmueble' => 0]) // O ajusta la ruta según corresponda
                    ->with('error', 'Debe ingresar desde un enlace válido para continuar.');
            }

            $request->validate([
                'id_inmueble_cliente' => 'nullable|integer',
                'nombre' => 'nullable|string',
                'fecha_desde' => 'nullable|date|before_or_equal:fecha_hasta',
                'fecha_hasta' => 'nullable|date|after_or_equal:fecha_desde',
            ]);

            // Obtener idInmueble desde la solicitud o la sesión
            $idInmueble = $request->id_inmueble_cliente ?? session('id_inmueble_cliente');

            // **Consultar Inmuebles primero** (Siempre debe tener datos)
            $inmuebles = InmuebleCliente::select('id_inmueble_cliente', 'sharepoint_id')
                ->when($idInmueble, function ($q) use ($idInmueble) {
                    return $q->where('sharepoint_id', $idInmueble);
                })
                ->distinct()
                ->get();

            // **Si no hay inmuebles, llenar con un valor vacío**
            if ($inmuebles->isEmpty()) {
                $inmuebles = collect([['id_inmueble_cliente' => null, 'sharepoint_id' => null]]);
            }

            // **Consultar Documentos (puede estar vacío)**
            $orderBy = in_array(request('sort_by'), ['fecha', 'nombre']) ? request('sort_by') : 'fecha';
            $orderDirection = request('order') === 'asc' ? 'asc' : 'desc';

            $documentos = Documento::when(!empty($idInmueble) && $idInmueble != 0, function ($q) use ($idInmueble) {
                    return $q->where('id_inmClient', $idInmueble);
                }, function ($q) {
                    return $q->whereRaw('1 = 0');
                })
                ->when($request->filled('nombre'), function ($q) use ($request) {
                    return $q->where('nombre', 'like', '%' . strip_tags($request->nombre) . '%');
                })
                ->when($request->filled('fecha_desde'), function ($q) use ($request) {
                    return $q->whereDate('fecha', '>=', $request->fecha_desde);
                })
                ->when($request->filled('fecha_hasta'), function ($q) use ($request) {
                    return $q->whereDate('fecha', '<=', $request->fecha_hasta);
                })
                ->orderBy($orderBy, $orderDirection)
                ->paginate(15)
                ->appends(request()->query());


            return view('Doc.Comercial.index', compact('documentos', 'inmuebles', 'email'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ocurrió un error inesperado. Inténtalo de nuevo.');
        }
    }

    // Método para filtrar inmuebles por su ID
    public function filtrarInmuebles(Request $request)
    {
        $query = $request->input('query');

        $inmuebles = InmuebleCliente::where('id_inmueble_cliente', 'like', '%' . $query . '%')
            ->limit(10)
            ->get();

        return response()->json($inmuebles);
    }

    public function Actualizar(Request $request)
    {
        try {
            $client = new Client();
            $accessToken = AzureService::getAccessToken();

            if (!$accessToken) {
                return response()->json(['error' => 'No se pudo obtener el token'], 500);
            }

            $documentosEditados = array_filter(explode(',', $request->input('documentos_editados')));
            $datosDocumentos = $request->input('documentos');
            $documentosActualizados = array_intersect_key($datosDocumentos, array_flip($documentosEditados));

            $driveId = 'b!dwijMLLwFE6_66lgi14StS9TjA7VcsJFrZY2Jkxrjwl2mEq_YJEsTZYQHnwHreW6';

            foreach ($documentosActualizados as $id => $datos) {
                try {
                    $driveItemId = $datos['IdSharepoint'];

                    $extension = $datos['Tipo'];
                    $nombreUnico = str_replace(['/', '\\', '-', ':', '*', '?', '"', '<', '>', '|'], '', substr(Str::uuid(), 0, 10));
                    $nombreLimpio = preg_replace('/[\\\\\/:*?"<>|#{}%&~]/u', '', pathinfo($datos['nombre'], PATHINFO_FILENAME));

                    $nuevoNombre = "{$nombreUnico} {$nombreLimpio}.{$extension}";

                    // Convertir a mayúsculas
                    $nuevoNombre = mb_strtoupper($nuevoNombre, 'UTF-8');

                    $url = "https://graph.microsoft.com/v1.0/drives/{$driveId}/items/{$driveItemId}/listItem/fields";

                    // Formateamos la fecha correctamente para MySQL

                    $body = [
                        'Carpeta' => 'Inmueble y Cliente',
                        'NombreArchivo' => $nuevoNombre,
                        'Fecha' => $datos['fecha'],
                        'CodDocum' => $datos['cod_documental'],
                    ];

                    // 1️⃣ Enviar actualización a SharePoint
                    $response = $client->patch($url, [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $accessToken,
                            'Content-Type' => 'application/json',
                        ],
                        'json' => $body,
                    ]);

                    $urlRename = "https://graph.microsoft.com/v1.0/drives/{$driveId}/items/{$driveItemId}";
                    $bodyRename = [
                        'name' => $nuevoNombre,
                    ];

                    $response = $client->patch($urlRename, [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $accessToken,
                            'Content-Type' => 'application/json',
                        ],
                        'json' => $bodyRename,
                    ]);

                    $responseData = json_decode($response->getBody(), true);

                    if ($response->getStatusCode() === 200) {
                        // 2️⃣ Actualizar en MySQL solo si SharePoint fue exitoso
                        DB::table('documentos')
                            ->where('id', $id)
                            ->update([
                                'nombre' => mb_strtoupper($datos['nombre']),
                                'fecha' => $datos['fecha'],
                                'cod_documental' => $datos['cod_documental'],
                                'url' => $responseData['webUrl'] ?? null, // Guardar el enlace
                            ]);

                        Log::info("Documento {$datos['nombre']} actualizado en SharePoint y MySQL.");
                    } else {
                        Log::error("Error al actualizar {$datos['nombre']} en SharePoint. Código: " . $response->getStatusCode());
                    }
                } catch (\Exception $e) {
                    Log::error("Error en documento {$datos['nombre']}: " . $e->getMessage());
                }
            }

            session()->flash('success', 'Operación realizada con éxito');
            return redirect()->back();
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $errorResponse = $e->getResponse();
            $errorDetails = json_decode($errorResponse->getBody(), true);
            return response()->json(['error' => $errorDetails], 500);
        }
    }

    public function ActualizarIndiv(Request $datos)
    {
        try {
            $client = new Client();
            $accessToken = AzureService::getAccessToken();

            if (!$accessToken) {
                return response()->json(['error' => 'No se pudo obtener el token'], 500);
            }

            $driveId = 'b!dwijMLLwFE6_66lgi14StS9TjA7VcsJFrZY2Jkxrjwl2mEq_YJEsTZYQHnwHreW6';

            try {
                $driveItemId = $datos['drive_item_id'];
                $extension = $datos['ext'];
                $nombreUnico = str_replace(['/', '\\', '-', ':', '*', '?', '"', '<', '>', '|'], '', substr(Str::uuid(), 0, 10));
                $nombreLimpio = preg_replace('/[\\\\\/:*?"<>|#{}%&~]/u', '', pathinfo($datos['nombre'], PATHINFO_FILENAME));

                $nuevoNombre = "{$nombreUnico} {$nombreLimpio}.{$extension}";

                // Convertir a mayúsculas
                $nuevoNombre = mb_strtoupper($nuevoNombre, 'UTF-8');

                $url = "https://graph.microsoft.com/v1.0/drives/{$driveId}/items/{$driveItemId}/listItem/fields";

                // Formateamos la fecha correctamente para MySQL

                $body = [
                    'Carpeta' => 'Inmueble y Cliente',
                    'NombreArchivo' => $nuevoNombre,
                    'Fecha' => $datos['fecha'],
                    'CodDocum' => $datos['cod_documental'],
                ];

                // 1️⃣ Enviar actualización a SharePoint
                $response = $client->patch($url, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => $body,
                ]);

                $urlRename = "https://graph.microsoft.com/v1.0/drives/{$driveId}/items/{$driveItemId}";
                $bodyRename = [
                    'name' => $nuevoNombre,
                ];

                $response = $client->patch($urlRename, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => $bodyRename,
                ]);

                $responseData = json_decode($response->getBody(), true);

                if ($response->getStatusCode() === 200) {
                    // 2️⃣ Actualizar en MySQL solo si SharePoint fue exitoso
                    DB::table('documentos')
                        ->where('id', $datos['id'])
                        ->update([
                            'nombre' => mb_strtoupper($datos['nombre']),
                            'fecha' => $datos['fecha'],
                            'cod_documental' => $datos['cod_documental'],
                            'url' => $responseData['webUrl'] ?? null, // Guardar el enlace
                        ]);

                    Log::info("Documento {$datos['nombre']} actualizado en SharePoint y MySQL.");
                } else {
                    Log::error("Error al actualizar {$datos['nombre']} en SharePoint. Código: " . $response->getStatusCode());
                }
            } catch (\Exception $e) {
                Log::error("Error en documento {$datos['nombre']}: " . $e->getMessage());
            }

            session()->flash('success', 'Operación realizada con éxito');
            return redirect()->back();
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $errorResponse = $e->getResponse();
            $errorDetails = json_decode($errorResponse->getBody(), true);
            return response()->json(['error' => $errorDetails], 500);
        }
    }

    public function VistaSubirDocumentos($idInmueble)
    {
        try {
            // Validar que el idInmueble sea un número
            if (!is_numeric($idInmueble)) {
                return redirect()->back()->with('error', 'El inmueble seleccionado no es válido.');
            }

            // Obtener inmuebles relacionados
            $inmuebles = InmuebleCliente::select('id_inmueble_cliente', 'sharepoint_id')->where('sharepoint_id', $idInmueble)->distinct()->get();

            return view('Doc.Comercial.Añadir', compact('inmuebles'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ocurrió un error inesperado. Inténtalo de nuevo.');
        }
    }

    //Cargar Documentos
    public function CargarDocumentosold(Request $request)
    {
        $request->validate([
            'documentos.*.archivo' => 'required|file',
            'documentos.*.nombre' => 'required|string|max:255',
            'documentos.*.cod_documental' => 'string|max:255',
            'documentos.*.fecha' => 'required|date',
        ]);

        $client = new Client();
        $accessToken = AzureService::getAccessToken();

        if (!$accessToken) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'No se pudo obtener el token de autenticación'])
                ->withInput();
        }

        $driveId = 'b!dwijMLLwFE6_66lgi14StS9TjA7VcsJFrZY2Jkxrjwl2mEq_YJEsTZYQHnwHreW6';
        $errores = [];

        foreach ($request->input('documentos') as $key => $documentoData) {
            try {
                $archivo = $request->file("documentos.{$key}.archivo");

                if (!$archivo) {
                    $errores[] = "No hay archivo en la posición {$key}";
                    continue;
                }

                $nombreFinal = $this->generarNombreArchivo($documentoData['nombre'], $archivo);
                $fileName = "{$documentoData['nombre']}.{$archivo->getClientOriginalExtension()}";
                $uploadResponse = $this->subirArchivo($client, $accessToken, $driveId, $nombreFinal, $archivo);

                if (!$uploadResponse) {
                    $errores[] = "Error al subir archivo en la posición {$key}";
                    continue;
                }

                $documento = Documento::create([
                    'nombre' => $fileName,
                    'cod_documental' => $documentoData['cod_documental'],
                    'fecha' => $documentoData['fecha'],
                    'url' => $uploadResponse['webUrl'],
                    'download_url' => $uploadResponse['@microsoft.graph.downloadUrl'],
                    'drive_item_id' => $uploadResponse['id'],
                    'mime_type' => $uploadResponse['file']['mimeType'],
                    'size' => $uploadResponse['size'],
                    'id_inmClient' => $documentoData['id_inmClient'],
                    'id_expInm' => 0,
                    'id_hisClient' => 0,
                ]);

                $metadataResponse = $this->actualizarMetadatos($client, $accessToken, $driveId, $uploadResponse['id'], $documento, $documentoData);

                if (!$metadataResponse) {
                    $errores[] = "Error al actualizar metadatos en la posición {$key}";
                    continue;
                }

                Documento::where('drive_item_id', $uploadResponse['id'])->update([
                    'id_carpeta' => $metadataResponse['id'],
                    'file_type' => $metadataResponse['DocIcon'],
                ]);
            } catch (\Exception $e) {
                $errores[] = "Error en documento {$key}: " . $e->getMessage();
            }
        }

        if (!empty($errores)) {
            return redirect()
                ->back()
                ->withErrors(['error' => $errores])
                ->withInput();
        }

        Session::flash('success', 'Documentos subidos y actualizados con éxito en SharePoint');
        return redirect()->back();
    }

    public function CargarDocumentos(Request $request)
    {
        $request->validate([
            'documentos.*' => 'required|file|max:10240', // 10MB máximo por archivo
        ]);

        $inmcliente = $request['id_inmueble_cliente'];
        $client = new Client();
        $accessToken = AzureService::getAccessToken();

        if (!$accessToken) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'No se pudo obtener el token de autenticación'])
                ->withInput();
        }

        $driveId = 'b!dwijMLLwFE6_66lgi14StS9TjA7VcsJFrZY2Jkxrjwl2mEq_YJEsTZYQHnwHreW6';
        $errores = [];


        foreach ($request->file('documentos') as $key => $documentoData) {
            try {
                $Nombre = $documentoData->getClientOriginalName();

                if (!$Nombre) {
                    $errores[] = "Error con el archivo {$Nombre}";
                    continue;
                }
                $Nombre = $documentoData->getClientOriginalName();
                $archivo = pathinfo($Nombre, PATHINFO_FILENAME);
                $extension = pathinfo($Nombre, PATHINFO_EXTENSION);

                $nombreFinal = $this->generarNombreArchivo($archivo, $extension);

                $fileName = $archivo;

                $uploadResponse = $this->subirArchivo($client, $accessToken, $driveId, $nombreFinal, $documentoData);

                if (!$uploadResponse) {
                    $errores[] = "Error al subir archivo en la posición {$Nombre}";
                    continue;
                }

                $documento = Documento::create([
                    'nombre' => $fileName,
                    'cod_documental' => 0,
                    'fecha' => null,
                    'url' => $uploadResponse['webUrl'],
                    'download_url' => $uploadResponse['@microsoft.graph.downloadUrl'],
                    'drive_item_id' => $uploadResponse['id'],
                    'mime_type' => $uploadResponse['file']['mimeType'],
                    'size' => $uploadResponse['size'],
                    'id_inmClient' => $inmcliente,
                    'id_expInm' => 0,
                    'id_hisClient' => 0,
                ]);

                $metadataResponse = $this->actualizarMetadatos($client, $accessToken, $driveId, $uploadResponse['id'], $documento, $inmcliente);

                if (!$metadataResponse) {
                    $errores[] = "Error al actualizar metadatos en la posición {$Nombre}";
                    continue;
                }

                Documento::where('drive_item_id', $uploadResponse['id'])->update([
                    'id_carpeta' => $metadataResponse['id'],
                    'file_type' => $metadataResponse['DocIcon'],
                ]);
            } catch (\Exception $e) {
                $errores[] = "Error en documento {$Nombre}: " . $e->getMessage();
            }
        }

        if (!empty($errores)) {
            return redirect()
                ->back()
                ->withErrors(['error' => $errores])
                ->withInput();
        }

        Session::flash('success', 'Documentos subidos y actualizados con éxito en SharePoint');
        return redirect()->back();
    }

    /**
     * Genera un nombre de archivo limpio y único.
     */
    private function generarNombreArchivo($nombreOriginal, $archivo)
    {
        $nombreUnico = str_replace(['/', '\\', '-', ':', '*', '?', '"', '<', '>', '|'], '', substr(Str::uuid(), 0, 10));
        $nombreLimpio = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '', $nombreOriginal);
        return "{$nombreUnico} {$nombreLimpio}.{$archivo}";
    }

    private function generarNombreArchivoold($nombreOriginal, $archivo)
    {
        $nombreUnico = str_replace(['/', '\\', '-', ':', '*', '?', '"', '<', '>', '|'], '', substr(Str::uuid(), 0, 10));
        $nombreLimpio = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '', $nombreOriginal);
        return "{$nombreUnico} {$nombreLimpio}.{$archivo->getClientOriginalExtension()}";
    }

    /**
     * Sube el archivo a Microsoft Graph API.
     */
    private function subirArchivo($client, $accessToken, $driveId, $nombreFinal, $archivo)
    {
        try {
            $uploadUrl = "https://graph.microsoft.com/v1.0/drives/{$driveId}/root:/{$nombreFinal}:/content?[email protected]=rename";
            $rutaTemporal = $archivo->getPathname();
            $mimeType = $archivo->getMimeType();

            $response = $client->put($uploadUrl, [
                'headers' => [
                    'Authorization' => "Bearer {$accessToken}",
                    'Content-Type' => $mimeType,
                ],
                'body' => fopen($rutaTemporal, 'r'), // Aquí va el contenido
            ]);

            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            dd([
                'error' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
                'codigo' => $e->getCode(),
            ]);
        }
    }

    /**
     * Actualiza los metadatos del archivo en SharePoint.
     */
    private function actualizarMetadatos($client, $accessToken, $driveId, $itemId, $documento, $documentoData)
    {
        try {
            $url = "https://graph.microsoft.com/v1.0/drives/{$driveId}/items/{$itemId}/listItem/fields";

            $body = [
                'Carpeta' => 'Inmueble y Cliente',
                'NombreArchivo' => $documento->nombre,
                'Fecha' => $documento->fecha,
                'Fecha_x0020_Archivo' => $documento->fecha,
                'CodDocum' => $documento->cod_documental,
                'IdInmClient' => $documentoData,
                'IDCarpeta' => 3,
            ];

            $response = $client->patch($url, [
                'headers' => [
                    'Authorization' => "Bearer {$accessToken}",
                    'Content-Type' => 'application/json',
                ],
                'json' => $body,
            ]);

            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            dd([
                'error' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
                'codigo' => $e->getCode(),
            ]);
        }
    }

    public function EliminarDocumento(Request $request)
    {
        try {
            // 🔹 Validar que haya documentos seleccionados
            if (!$request->has('documentos') || empty($request->documentos)) {
                return redirect()
                    ->back()
                    ->withErrors(['error' => 'No se seleccionaron documentos para eliminar.']);
            }

            // 🔹 Obtener el access token de Azure
            $accessToken = AzureService::getAccessToken();
            if (!$accessToken) {
                return redirect()
                    ->back()
                    ->withErrors(['error' => 'No se pudo obtener el token de autenticación.']);
            }

            $client = new Client();
            $driveId = 'b!dwijMLLwFE6_66lgi14StS9TjA7VcsJFrZY2Jkxrjwl2mEq_YJEsTZYQHnwHreW6';
            $errores = [];
            $eliminados = 0;

            // 🔹 Recorrer los documentos seleccionados y eliminarlos
            foreach ($request->documentos as $id) {
                try {
                    // 🔹 Buscar el documento en la base de datos
                    $documento = Documento::findOrFail($id);

                    // 🔹 Eliminar el documento en SharePoint
                    $deleteUrl = "https://graph.microsoft.com/v1.0/drives/{$driveId}/items/{$documento->drive_item_id}";

                    try {
                        $response = $client->delete($deleteUrl, [
                            'headers' => [
                                'Authorization' => "Bearer {$accessToken}",
                            ],
                        ]);

                        if ($response->getStatusCode() !== 204) {
                            throw new \Exception("No se pudo eliminar el documento {$documento->nombre} en SharePoint.");
                        }
                    } catch (\GuzzleHttp\Exception\ClientException $e) {
                        // Si SharePoint devuelve 404, ignoramos el error y continuamos con MySQL
                        if ($e->getResponse()->getStatusCode() !== 404) {
                            throw $e;
                        }
                    }

                    // 🔹 Eliminar el documento en MySQL
                    $documento->delete();
                    $eliminados++;
                } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                    $errores[] = "El documento con ID {$id} no existe en la base de datos.";
                } catch (\Exception $e) {
                    $errores[] = "Error al eliminar el documento con ID {$id}: " . $e->getMessage();
                }
            }

            // 🔹 Devolver resultados
            if ($eliminados > 0) {
                Session::flash('success', "{$eliminados} documento(s) eliminado(s) correctamente.");
            }

            if (!empty($errores)) {
                return redirect()->back()->withErrors($errores);
            }

            return redirect()->back();
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'Error general: ' . $e->getMessage()]);
        }
    }

    public function VistaEliminarDocumento(Request $request, $idInmueble)
    {
        try {
            // Validar que el idInmueble sea un número y exista en la tabla documentos
            if (!is_numeric($idInmueble) || !Documento::where('id_inmClient', $idInmueble)->exists()) {
                return redirect()->back()->with('error', 'El inmueble seleccionado no es válido.');
            }

            // Crear la consulta base
            $query = Documento::where('id_inmClient', $idInmueble);

            // Aplicar filtro por nombre si está presente
            if ($request->filled('nombre')) {
                $query->where('nombre', 'LIKE', '%' . $request->nombre . '%');
            }

            // Aplicar filtro por fecha desde
            if ($request->filled('fecha_desde')) {
                $query->whereDate('created_at', '>=', $request->fecha_desde);
            }

            // Aplicar filtro por fecha hasta
            if ($request->filled('fecha_hasta')) {
                $query->whereDate('created_at', '<=', $request->fecha_hasta);
            }

            // Obtener documentos filtrados con paginación
            $documentos = $query->paginate(15);

            // Obtener inmuebles relacionados
            $inmuebles = InmuebleCliente::select('id_inmueble_cliente', 'sharepoint_id')->where('sharepoint_id', $idInmueble)->distinct()->get();

            return view('Doc.Comercial.Delete', compact('documentos', 'inmuebles', 'idInmueble'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ocurrió un error inesperado. Inténtalo de nuevo.');
        }
    }
}
