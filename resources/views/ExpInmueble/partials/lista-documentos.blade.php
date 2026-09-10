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
                <td>{{ $inmueble->NOMENCLATURA }}</td>
                @if (session('documental'))
                    <td class="text-end pe-2" style="white-space: nowrap;"> {{-- Botones alineados a la derecha --}}
                        <a href="{{ route('ExpInmueble.edit', $inmueble->idExpInmueble) }}" class="btn btn-sm btn-warning">
                            <i class="bi bi-pencil"></i> Editar
                        </a>
                        <a href="{{ route('ExpInmueble.VistaSubir', $inmueble->idExpInmueble) }}" class="btn btn-sm btn-success">
                            <i class="bi bi-file-earmark-plus"></i> Añadir
                        </a>
                        <a href="{{ route('ExpInmueble.VistaDelete', $inmueble->idExpInmueble) }}" class="btn btn-sm btn-danger">
                            <i class="bi bi-trash"></i> Eliminar
                        </a>
                    </td>
                @endif
            </tr>
        @empty
            <tr>
                <td colspan="{{ session('documental') ? 2 : 1 }}" class="text-center text-muted">No hay inmuebles seleccionados</td>
            </tr>
        @endforelse
    </tbody>
</table>



<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>Fecha</th>
                <th>Nombre</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($documentos as $doc)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($doc->fecha)->format('d.m.Y') }}</td>
                    <td>{{ $doc->nombre }}</td>
                    <td>
                        <div class="btn-group">
                            <button class="btn btn-outline-primary btn-sm" onclick="abrirPDF('{{ asset($doc->url) }}')">
                                <i class="bi bi-eye"></i> Ver
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">No hay documentos disponibles.</td>
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
