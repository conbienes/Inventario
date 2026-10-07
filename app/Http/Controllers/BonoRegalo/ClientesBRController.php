<?php

namespace App\Http\Controllers\BonoRegalo;

use App\Http\Controllers\Controller;
use App\Http\Requests\BonoRegalo\ClienteBonoRequest;
use App\Jobs\BonoRegalo\SincronizarClienteBCJob;
use App\Models\BonoRegalo\ClienteBonoR;
use App\Services\BonoRegalo\ClienteBCSyncService;
use Illuminate\Http\Request;

class ClientesBRController extends Controller
{
    public function index()
    {
        return view('BonoRegalo.Clientes.index', $this->catalogos());
    }

    public function BuscarCliente(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $clientes = ClienteBonoR::query()
            ->when($search !== '', function ($query) use ($search) {
                // Agrupado para que futuros filtros no se mezclen con los OR
                $query->where(function ($q) use ($search) {
                    $q->where('cedula', 'like', "%{$search}%")
                        ->orWhere('nombre', 'like', "%{$search}%")
                        ->orWhere('apellidos', 'like', "%{$search}%")
                        ->orWhere('razons', 'like', "%{$search}%")
                        ->orWhere('correo', 'like', "%{$search}%");
                });
            })
            ->paginate(15)
            ->appends($request->query());

        return view('BonoRegalo.Clientes.BuscarCliente', compact('clientes'));
    }

    public function store(ClienteBonoRequest $request, ClienteBCSyncService $syncService)
    {
        try {
            $cliente = ClienteBonoR::create($request->datosCliente());
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withErrors(['error' => 'No se pudo guardar el cliente. Intenta de nuevo o contacta a soporte.'])
                ->withInput();
        }

        // Sincronización con BC: en cola si BC_ASYNC=true, si no en esta misma petición.
        // Un fallo de BC no impide el registro: el cliente queda pendiente y se reintenta desde Contabilidad.
        if (config('services.bc.async')) {
            SincronizarClienteBCJob::dispatch($cliente->id);
            $msgBC = 'Se sincronizará con BC en unos minutos.';
        } else {
            $msgBC = $syncService->sincronizar($cliente)['ok']
                ? 'Sincronizado con BC.'
                : 'Pendiente por BC (se puede reintentar desde Contabilidad › Clientes).';
        }

        return redirect()
            ->route('BonoRegalo.IndexFacturas')
            ->with('success', 'Cliente registrado. ' . $msgBC);
    }

    public function edit(string $id)
    {
        $cliente = ClienteBonoR::findOrFail($id);

        return view('BonoRegalo.Clientes.editarCliente', $this->catalogos() + compact('cliente'));
    }

    public function update(ClienteBonoRequest $request, string $id)
    {
        $cliente = ClienteBonoR::findOrFail($id);

        try {
            $cliente->fill($request->datosCliente())->save();
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withErrors(['error' => 'No se pudo actualizar el cliente. Intenta de nuevo o contacta a soporte.'])
                ->withInput();
        }

        if (!$cliente->wasChanged()) {
            return back()->with('success', 'No se detectaron cambios para actualizar.')->withInput();
        }

        return redirect()->route('BonoRegalo.BuscarCliente')->with('success', 'Cliente actualizado correctamente.');
    }

    private function catalogos(): array
    {
        return [
            'tiposDocumento' => config('bono_regalo.tipos_documento'),
            'tiposActividad' => config('bono_regalo.tipos_actividad'),
        ];
    }
}
