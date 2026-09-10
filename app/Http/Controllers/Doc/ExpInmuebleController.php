<?php

namespace App\Http\Controllers\Doc;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use App\Services\AzureService;
use App\Models\documento;
use App\Models\ExpInmueble;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class ExpInmuebleController extends Controller
{
    public function Editar(Request $request, $idInmueble)
    {
        try {
            // Validar que el idInmueble sea un número y exista en la tabla documentos
            if (!is_numeric($idInmueble) || !Documento::where('id_expInm', $idInmueble)->exists()) {
                return redirect()->back()->with('error', 'El inmueble seleccionado no es válido.');
            }

            // Obtener documentos con filtros
            $query = Documento::where('id_expInm', $idInmueble);

            if ($request->filled('nombre')) {
                $query->where('nombre', 'LIKE', '%' . $request->nombre . '%');
            }

            if ($request->filled('fecha_desde')) {
                $query->whereDate('fecha', '>=', $request->fecha_desde);
            }

            if ($request->filled('fecha_hasta')) {
                $query->whereDate('fecha', '<=', $request->fecha_hasta);
            }

            // Ordenar por fecha descendente (de más reciente a más antigua)
            $documentos = $query->orderBy('fecha', 'desc')->paginate(15);

            $documentos = $query->paginate(15);

            // Obtener inmuebles relacionados
            $inmuebles = ExpInmueble::select('ACTIVO', 'idExpInmueble', 'NOMENCLATURA', 'Tipoinm')->where('idExpInmueble', $idInmueble)->distinct()->get();

            return view('Doc.ExpInmueble.Edit', compact('documentos', 'inmuebles', 'idInmueble'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ocurrió un error inesperado. Inténtalo de nuevo.');
        }
    }

    public function Inicio2(Request $request)
    {
        dd('La función Inicio se está ejecutando correctamente');
    }

    public function Inicio(Request $request)
    {
        try {
            // Verificar si el correo está en la sesión
            $email = session('user_email');
            if (!$email) {
                return redirect()
                    ->route('Siim.ExpInmueble', ['idInmueble' => 0])
                    ->with('error', 'Debe ingresar desde un enlace válido para continuar.');
            }

            // Validar el request
            $request->validate([
                'ExpInmueble' => 'nullable|integer',
                'nombre' => 'nullable|string',
                'fecha_desde' => 'nullable|date|before_or_equal:fecha_hasta',
                'fecha_hasta' => 'nullable|date|after_or_equal:fecha_desde',
            ]);

            // Obtener idInmueble desde el request o la sesión
            $idInmueble = $request->ExpInmueble ?? session('ExpInmueble');

            // Definir ordenamiento dinámico
            $orderBy = in_array(request('sort_by'), ['fecha', 'nombre']) ? request('sort_by') : 'fecha';
            $orderDirection = request('order') === 'asc' ? 'asc' : 'desc';

            $documentos = Documento::when(!empty($idInmueble) && $idInmueble != 0, function ($q) use ($idInmueble) {
                return $q->where('id_expInm', $idInmueble);
            }, function ($q) {
                return $q->whereRaw('1 = 0'); // No devuelve resultados si no hay inmueble válido
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
            ->paginate(15);

            // Cargar info del inmueble (si existe)
            $inmuebles = ExpInmueble::select('ACTIVO', 'idExpInmueble', 'NOMENCLATURA', 'Tipoinm')
                ->where('idExpInmueble', $idInmueble)
                ->distinct()
                ->get();

            return view('Doc.ExpInmueble.index', compact('documentos', 'inmuebles', 'email'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ocurrió un error inesperado. Inténtalo de nuevo.');
        }
    }

    // Método para filtrar inmuebles por su ID
    public function filtrarExpInmueble(Request $request)
    {
        $query = $request->input('query');

        $inmuebles = ExpInmueble::where('NOMENCLATURA', 'like', '%' . $query . '%')
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

                    // Extraer la extensión del archivo
                    $extension = $datos['Tipo'];
                    $nombreUnico = str_replace(['/', '\\', '-', ':', '*', '?', '"', '<', '>', '|'], '', substr(Str::uuid(), 0, 10));
                    $nombreLimpio = preg_replace('/[\\\\\/:*?"<>|#{}%&~]/u', '', pathinfo($datos['nombre'], PATHINFO_FILENAME));

                    $nuevoNombre = "{$nombreUnico} {$nombreLimpio}.{$extension}";

                    // Convertir a mayúsculas
                    $nuevoNombre = mb_strtoupper($nuevoNombre, 'UTF-8');

                    // **1️⃣ Actualizar metadatos**
                    $urlMetadata = "https://graph.microsoft.com/v1.0/drives/{$driveId}/items/{$driveItemId}/listItem/fields";
                    $bodyMetadata = [
                        'Carpeta' => 'Expediente Inmueble',
                        'NombreArchivo' => $nuevoNombre,
                        'Fecha' => $datos['fecha'],
                        'CodDocum' => $datos['cod_documental'],
                    ];

                    $client->patch($urlMetadata, [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $accessToken,
                            'Content-Type' => 'application/json',
                        ],
                        'json' => $bodyMetadata,
                    ]);

                    // **2️⃣ Renombrar el archivo**
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

    public function VistaSubirDocumentos($idInmueble)
    {
        try {
            // Validar que el idInmueble sea un número
            if (!is_numeric($idInmueble)) {
                return redirect()->back()->with('error', 'El inmueble seleccionado no es válido.');
            }

            // Obtener inmuebles relacionados
            $inmuebles = ExpInmueble::select('ACTIVO', 'idExpInmueble', 'NOMENCLATURA', 'Tipoinm')->where('idExpInmueble', $idInmueble)->distinct()->get();

            return view('Doc.ExpInmueble.Añadir', compact('inmuebles'));
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
            'documentos.*.cod_documental' => 'required|string|max:255',
            'documentos.*.fecha' => 'required|date',
        ]);
        $datos = $request->all();

        $datos = $request->input('documentos', []); // Extraer el array "documentos" del request

        $id_inmClient = collect($datos)
            ->pluck('id_inmClient')
            ->filter()
            ->first();

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
                    'id_inmClient' => 0,
                    'id_expInm' => $id_inmClient,
                    'id_hisClient' => 0,
                ]);

                $metadataResponse = $this->actualizarMetadatos($client, $accessToken, $driveId, $uploadResponse['id'], $documento, $id_inmClient);

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

        $id_inmClient = $request['id_inmueble'];
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

        foreach ($request->file('documentos')  as $key => $documentoData) {
            try {
                $Nombre = $documentoData->getClientOriginalName();

                if (!$Nombre) {
                    $errores[] = "No hay archivo en la posición {$Nombre}";
                    continue;
                }

                $archivo = pathinfo($Nombre, PATHINFO_FILENAME);
                $extension = pathinfo($Nombre, PATHINFO_EXTENSION);

                $nombreFinal = $this->generarNombreArchivo($archivo, $extension);
                $fileName = $archivo;
                $uploadResponse = $this->subirArchivo($client, $accessToken, $driveId, $nombreFinal, $documentoData);

                if (!$uploadResponse) {
                    $errores[] = "Error al subir archivo en la posición {$key}";
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
                    'id_inmClient' => 0,
                    'id_expInm' => $id_inmClient,
                    'id_hisClient' => 0,
                ]);

                $metadataResponse = $this->actualizarMetadatos($client, $accessToken, $driveId, $uploadResponse['id'], $documento, $id_inmClient);

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

    /**
     * Genera un nombre de archivo limpio y único.
     */
    private function generarNombreArchivo($nombreOriginal, $archivo)
    {
        $nombreUnico = str_replace(['/', '\\', '-', ':', '*', '?', '"', '<', '>', '|'], '', substr(Str::uuid(), 0, 10));
        $nombreLimpio = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '', $nombreOriginal);
        return "{$nombreUnico} {$nombreLimpio}.{$archivo}";
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
                'Carpeta' => 'Expediente Inmueble',
                'NombreArchivo' => $documento->nombre,
                'Fecha' => $documento->fecha,
                'Fecha_x0020_Archivo' => $documento->fecha,
                'CodDocum' => $documento->cod_documental,
                'NIT' => $documentoData,
                'IDCarpeta' => 1,
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
            // Mostrar error con detalles
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
                    $errores[] = "El documento con ID {$id} no existe.";
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
            if (!is_numeric($idInmueble) || !Documento::where('id_expInm', $idInmueble)->exists()) {
                return redirect()->back()->with('error', 'El inmueble seleccionado no es válido.');
            }

            // Obtener documentos con filtros
            $query = Documento::where('id_expInm', $idInmueble);

            if ($request->filled('nombre')) {
                $query->where('nombre', 'LIKE', '%' . $request->nombre . '%');
            }

            if ($request->filled('fecha_desde')) {
                $query->whereDate('created_at', '>=', $request->fecha_desde);
            }

            if ($request->filled('fecha_hasta')) {
                $query->whereDate('created_at', '<=', $request->fecha_hasta);
            }

            $documentos = $query->paginate(15);

            // Obtener inmuebles relacionados
            $inmuebles = ExpInmueble::select('ACTIVO', 'idExpInmueble', 'NOMENCLATURA', 'Tipoinm')->where('idExpInmueble', $idInmueble)->distinct()->get();

            return view('Doc.ExpInmueble.Delete', compact('documentos', 'inmuebles', 'idInmueble'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ocurrió un error inesperado. Inténtalo de nuevo.');
        }
    }
}
