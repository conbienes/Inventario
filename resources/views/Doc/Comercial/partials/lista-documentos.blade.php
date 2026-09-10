<table class="table table-striped table-bordered text-center">
    <thead class="table-primary">
        <tr>
            <th>ID Inmueble</th>
            @if (session('documental'))
                <th class="text-center" style="width: 10%;">Acción</th> {{-- Reducir ancho y centrar título --}}
            @endif
        </tr>
    </thead>
    <tbody>
        @forelse ($inmuebles as $inmueble)
            <tr>
                <td>({{ $inmueble->sharepoint_id }}) - {{ $inmueble->id_inmueble_cliente }}</td>
                @if (session('documental'))
                    <td class="text-end pe-2" style="white-space: nowrap;"> {{-- Botones alineados a la derecha --}}
                        <a href="{{ route('Comercial.VistaSubir', $inmueble->sharepoint_id) }}"
                            class="btn btn-sm btn-success">
                            <i class="bi bi-file-earmark-plus"></i> Añadir
                        </a>
                        <a href="{{ route('Comercial.edit', $inmueble->sharepoint_id ) }}" class="btn btn-sm btn-warning">
                            <i class="bi bi-pencil"></i> Editar
                        </a>
                        <a href="{{ route('Comercial.VistaDelete', $inmueble->sharepoint_id) }}"
                            class="btn btn-sm btn-danger">
                            <i class="bi bi-trash"></i> Eliminar
                        </a>
                    </td>
                @endif
            </tr>
        @empty
            <tr>
                <td colspan="{{ session('documental') ? 2 : 1 }}" class="text-center text-muted">No hay inmuebles
                    seleccionados</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>
                    <a class="text-white text-decoration-none"
                       href="{{ request()->fullUrlWithQuery(['sort_by' => 'fecha', 'order' => request('order') === 'asc' ? 'desc' : 'asc']) }}">
                        Fecha
                        @if(request('sort_by') === 'fecha')
                            <i class="bi bi-arrow-{{ request('order') === 'asc' ? 'down' : 'up' }}"></i>
                        @endif
                    </a>
                </th>

                <th>
                    <a class="text-white text-decoration-none"
                       href="{{ request()->fullUrlWithQuery(['sort_by' => 'nombre', 'order' => request('order') === 'asc' ? 'desc' : 'asc']) }}">
                        Documento
                        @if(request('sort_by') === 'nombre')
                            <i class="bi bi-arrow-{{ request('order') === 'asc' ? 'down' : 'up' }}"></i>
                        @endif
                    </a>
                </th>
                <th>Ver</th>
                @if (session('documental'))
                    <th class="text-center">Acciones</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($inmuebles as $inmueble)
            @forelse($documentos as $doc)
                <tr>
                    <td>{{ $doc->fecha ? \Carbon\Carbon::parse($doc->fecha)->format('Y.m.d') : 'Sin fecha' }}</td>
                    <td>{{ $doc->nombre }}</td>
                    <td>
                        <button class="btn btn-outline-primary btn-sm" onclick="abrirPDF('{{ asset($doc->url) }}')">
                            <i class="bi bi-eye"></i> Ver
                        </button>
                    </td>
                    @if (session('documental'))
                        <td class="text-end pe-2">
                            <a href="{{ route('Comercial.EditarIndicidual', ['sharepoint_id' => $inmueble->sharepoint_id, 'Id' => $doc->id]) }}" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i> Editar
                            </a>

                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">No hay documentos disponibles.</td>
                </tr>
            @endforelse
        @empty
            <tr>
                <td colspan="4" class="text-center text-muted">No hay inmuebles disponibles.</td>
            </tr>
        @endforelse

        </tbody>
    </table>
</div>


<script>
    function abrirPDF(url) {
        window.open(url, '_blank');
    }
</script>
