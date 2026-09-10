<?php

namespace App\Http\Controllers\Doc;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use App\Services\AzureService;
use App\Models\documento;
use App\Models\ExpInmueble;
use Carbon\Carbon;
use App\Models\InmuebleCliente;
use App\Models\Seguridad\Usuario;

use Illuminate\Http\Request;
class FileController extends Controller
{

    // Método para obtener los elementos de una Listas en SharePoint
    public function GetListasInmuebleCliente()
    {
        // ✅ Crear cliente HTTP
        $client = new Client();
        $accessToken = AzureService::getAccessToken();

        if (!$accessToken) {
            return response()->json(['error' => 'No se pudo obtener el token de acceso'], 500);
        }

        // ✅ 1. Obtener el ID del sitio de SharePoint
        $siteData = AzureService::getSharePointSite();
        if (isset($siteData['error'])) {
            return response()->json($siteData, 500);
        }

        $siteId = $siteData['id'];

        // ✅ 2. Obtener todas las listas de SharePoint
        //$listId = $this->getListId($client, $siteId, $accessToken, 'Inmueble Cliente');
        $listId = $this->getListId($client, $siteId, $accessToken, 'Expedientes de Inmubles');

        if (!$listId) {
            return response()->json(['error' => 'No se encontró la lista'], 404);
        }

        // ✅ 3. Obtener los elementos de la lista
        $items = $this->getListItems($client, $siteId, $listId, $accessToken);

        if (isset($items['error'])) {
            return response()->json($items, 500);
        }

        foreach ($items as $item) {
            dd($item);
            InmuebleCliente::updateOrCreate(
                ['sharepoint_id' => data_get($item, 'id')], // Condición para buscar
                [
                    'content_type' => data_get($item, 'ContentType'),
                    'modified' => isset($item['Modified']) ? Carbon::parse($item['Modified'])->format('Y-m-d H:i:s') : null,
                    'created' => isset($item['Created']) ? Carbon::parse($item['Created'])->format('Y-m-d H:i:s') : null,
                    'cliente_id' => data_get($item, 'IdCliente'),
                    'inmueble_id' => data_get($item, 'IDINMUEBLE'),
                    'nit_cliente' => data_get($item, 'Nit_x0020_Cliente'),
                    'numero_local' => data_get($item, 'Numero_x0020_Local'),
                    'id_inmueble_cliente' => data_get($item, 'id_inmueble_cliente'),
                    'categoria' => data_get($item, 'Categoria', null),
                    'marca' => data_get($item, 'Marca'),
                    'estado_inmueble' => data_get($item, 'Estado_x0020_Inmeble'),
                    'codigo_bc' => data_get($item, 'C_x00f3_digo_x0020_BC'),
                    'cartera_pendiente' => data_get($item, 'CarteraPendiente'),
                    'fecha_inicio_contrato' => isset($item['Fechainiciocontrato0']) ? Carbon::parse($item['Fechainiciocontrato0'])->format('Y-m-d') : null,
                    'fecha_final_contrato' => isset($item['Fechafinaldelcontrato']) ? Carbon::parse($item['Fechafinaldelcontrato'])->format('Y-m-d') : null,
                    'nomenclatura_bc' => data_get($item, 'NomenclaturaBC'),
                    'centro_costos_bc' => data_get($item, 'CentrodeCostosBC'),
                    'tipo_garantia' => data_get($item, 'Tipodegarantia'),
                    'asegurado_canon' => data_get($item, 'AseguradoCanon'),
                    'asegurado_admon' => data_get($item, 'AseguradoAdmon'),
                    'tipo_canon' => data_get($item, 'Tipo_x0020_Canon'),
                    'valor_canon' => data_get($item, 'Valor_x0020_Canon'),
                    'fecha_inicio' => isset($item['Fecha_x0020_inicio']) ? Carbon::parse($item['Fecha_x0020_inicio'])->format('Y-m-d') : null,
                    'fecha_renovacion' => isset($item['Fecha_x0020_Renovacion']) ? Carbon::parse($item['Fecha_x0020_Renovacion'])->format('Y-m-d') : null,
                    'fecha_final' => isset($item['Fecha_x0020_Final']) ? Carbon::parse($item['Fecha_x0020_Final'])->format('Y-m-d') : null,
                    'renovaciones' => data_get($item, 'Renovaciones'),
                    'incremento' => data_get($item, 'Incremento'),
                    'comentario_canon' => data_get($item, 'Comentario_x0020_Canon'),
                ],
            );
        }

        return response()->json(['message' => 'Datos sincronizados correctamente'], 200);
    }

    public function GetListasExpedientesdeInmubles()
    {
        // ✅ Crear cliente HTTP
        $client = new Client();
        $accessToken = AzureService::getAccessToken();

        if (!$accessToken) {
            return response()->json(['error' => 'No se pudo obtener el token de acceso'], 500);
        }

        // ✅ 1. Obtener el ID del sitio de SharePoint
        $siteData = AzureService::getSharePointSite();
        if (isset($siteData['error'])) {
            return response()->json($siteData, 500);
        }

        $siteId = $siteData['id'];

        // ✅ 2. Obtener todas las listas de SharePoint

        $listId = $this->getListId($client, $siteId, $accessToken, 'Expedientes de Inmubles');

        if (!$listId) {
            return response()->json(['error' => 'No se encontró la lista'], 404);
        }

        // ✅ 3. Obtener los elementos de la lista
        $items = $this->getListItems($client, $siteId, $listId, $accessToken);

        if (isset($items['error'])) {
            return response()->json($items, 500);
        }

        foreach ($items as $item) {
            ExpInmueble::updateOrCreate(
                ['idExpInmueble' => data_get($item, 'id')], // Condición para buscar
                [
                    'NOMENCLATURA' => data_get($item, 'NOMENCLATURA'),
                    'piso' => data_get($item, 'piso_x002e_') ?? data_get($item, 'Piso_x0020_B'),
                    'Destinaciondelinmueble' => data_get($item, 'Destinaciondelinmueble'),
                    'ACTIVO' => data_get($item, 'ACTIVO', false),
                    'NRODELOCALES' => data_get($item, 'NRODELOCALES'),
                    'Tipoinm' => data_get($item, 'Tipoinm'),
                    'MILookupId' => data_get($item, 'MILookupId'),
                    'Areatotalconstruida' => data_get($item, 'Areatotalconstruida'),
                    'Areamezanine' => data_get($item, 'Areamezanine'),
                    'Areamesas' => data_get($item, 'Areamesas'),
                    'Otrasareas' => data_get($item, 'Otrasareas'),
                    'Propietario' => data_get($item, 'Propietario_x0020_B'),
                    'Direccion' => data_get($item, 'Direccion_x0020_Del_x0020_Inmueb'),
                    'Ubicacion_PH' => data_get($item, 'Ubicacion_x0020_PH_x0020_B'),
                    'Municipio' => data_get($item, 'Municipio_x0020_B'),
                    'Admon' => data_get($item, 'Admon'),
                    'Valor_M2' => data_get($item, 'Valor_x0020_M2'),
                    'Fecha_admon' => data_get($item, 'Fecha_x0020_admon') ? Carbon::parse(data_get($item, 'Fecha_x0020_admon'))->format('Y-m-d') : null, // Convierte a 'YYYY-MM-DD' o guarda NULL
                    'Lote' => data_get($item, 'Lote'),
                ],
            );
        }

        return response()->json(['message' => 'Datos sincronizados correctamente'], 200);
    }
    /**
     * Obtener el ID de una lista en SharePoint por su nombre.
     */
    private function getListId($client, $siteId, $accessToken, $listName)
    {
        try {
            $response = $client->get("https://graph.microsoft.com/v1.0/sites/$siteId/lists", [
                'headers' => [
                    'Authorization' => "Bearer $accessToken",
                    'Accept' => 'application/json',
                ],
            ]);

            $lists = json_decode($response->getBody(), true);
            foreach ($lists['value'] as $list) {
                if ($list['name'] === $listName) {
                    return $list['id']; // Retornar el ID de la lista encontrada
                }
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }
    /**
     * Obtener los elementos dentro de una lista de SharePoint.
     */
    private function getListItems($client, $siteId, $listId, $accessToken)
    {
        try {
            $items = [];
            $nextLink = "https://graph.microsoft.com/v1.0/sites/$siteId/lists/$listId/items?\$expand=fields";

            do {
                // Realizar la solicitud HTTP
                $response = $client->get($nextLink, [
                    'headers' => [
                        'Authorization' => "Bearer $accessToken",
                        'Accept' => 'application/json',
                    ],
                ]);

                // Convertir la respuesta a un array PHP
                $data = json_decode($response->getBody(), true);

                // Extraer solo los valores de las columnas y agregarlos al array principal
                foreach ($data['value'] as $item) {
                    $items[] = $item['fields'];
                }

                // Verificar si hay más páginas de datos
                $nextLink = $data['@odata.nextLink'] ?? null;
            } while ($nextLink); // Continuar mientras haya más páginas

            return $items;
        } catch (\Exception $e) {
            return ['error' => 'No se pudieron obtener los elementos de la lista: ' . $e->getMessage()];
        }
    }

    public function SiimComercial($idInmueble = null, Request $request, $email)
    {
        try {
            // Buscar el empleado por el correo
            $empleado = Usuario::where('Correo', $email)->first();

            if ($empleado) {
                // Guardar el correo en la sesión
                session(['user_email' => $email]);

                // Si Documental es 1, guardar true en la sesión
                if ($empleado->Documental == 1) {
                    session(['documental' => true]);
                } else {
                    session(['documental' => false]);
                }
            } else {
                // Si el usuario no existe, limpiar la sesión
                session()->forget(['user_email', 'documental']);
            }

            $request->merge(['id_inmueble_cliente' => $idInmueble, 'email' => $email]);

            $request->validate([
                'id_inmueble_cliente' => 'nullable|integer',
                'email' => 'nullable|email',
            ]);

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


            $inmuebles = InmuebleCliente::select('id_inmueble_cliente', 'sharepoint_id')
                ->when(
                    !empty($idInmueble) && $idInmueble != 0,
                    function ($query) use ($idInmueble) {
                        return $query->where('sharepoint_id', $idInmueble);
                    },
                    function ($query) {
                        return $query->whereRaw('1 = 0'); // Retorna una consulta vacía si no hay un ID válido
                    },
                )
                ->distinct()
                ->get();


            return view('Doc.Comercial.index', compact('documentos', 'inmuebles'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ocurrió un error inesperado. Inténtalo de nuevo.');
        }
    }

    public function SiimExpInmueble($idInmueble = null, Request $request, $email)
    {
        try {

            // Buscar el empleado por el correo
            $empleado = Usuario::where('Correo', $email)->first();

            if ($empleado) {
                // Guardar el correo en la sesión
                session(['user_email' => $email]);

                // Si Documental es 1, guardar true en la sesión
                if ($empleado->Documental == 1) {
                    session(['documental' => true]);
                } else {
                    session(['documental' => false]);
                }
            } else {
                // Si el usuario no existe, limpiar la sesión
                session()->forget(['user_email', 'documental']);
            }

            $request->merge(['ExpInmueble' => $idInmueble, 'email' => $email]);

            $request->validate([
                'ExpInmueble' => 'nullable|integer',
                'email' => 'nullable|email',
            ]);

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


            $inmuebles = ExpInmueble::select('ACTIVO', 'idExpInmueble','NOMENCLATURA','Tipoinm')
                ->when(
                    !empty($idInmueble) && $idInmueble != 0,
                    function ($query) use ($idInmueble) {
                        return $query->where('idExpInmueble', $idInmueble);
                    },
                    function ($query) {
                        return $query->whereRaw('1 = 0'); // Retorna una consulta vacía si no hay un ID válido
                    },
                )
                ->distinct()
                ->get();
                   // dd($inmuebles, $documentos );

            return view('Doc.ExpInmueble.index', compact('documentos', 'inmuebles'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ocurrió un error inesperado. Inténtalo de nuevo.');
        }
    }
}
