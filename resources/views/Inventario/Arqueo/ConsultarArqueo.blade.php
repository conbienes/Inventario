@extends("theme.$theme.layout")
@section('body-class', 'inv-page')
@include('Inventario.partials.estilos')

@section('content')
@php
    $primero = $Inventario->first();
    $idEmpresa = optional($primero)->IdEmpresa ?? divisionInventarioActual();
    $nombreEmpresa = optional($primero)->Division
        ?? optional(divisionesInventario()->firstWhere('id', $idEmpresa))->nombre;
@endphp

@if (session('succes'))
    <div class="alert alert-success d-flex align-items-center" role="alert">
        <i class="fas fa-check-circle fa-lg mr-3"></i>
        <div><strong>Arqueo cargado.</strong> {{ session('succes') }}</div>
    </div>
@endif
@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <strong><i class="fas fa-exclamation-triangle mr-1"></i> No se pudo cargar el archivo:</strong>
        <ul class="mb-0 pl-3 mt-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<section class="inv-hero">
    <span class="inv-hero__ico" aria-hidden="true"><i class="fas fa-clipboard-check"></i></span>
    <div class="inv-hero__txt">
        <span class="inv-hero__etiqueta">Arqueo de inventario</span>
        <h1>{{ $nombreEmpresa ?: 'Arqueo de inventario' }}</h1>
        <p>Compara lo que dice el sistema con lo que hay en bodega: descarga, cuenta y sube el conteo.</p>
    </div>
</section>

@include('Inventario.partials.selector-division', ['ruta' => 'Consultar_Arqueo_Empresa'])

<div class="row">
    {{-- Paso 1: descargar --}}
    <div class="col-lg-5 mb-3">
        <div class="inv-tarjeta h-100 d-flex flex-column">
            <div class="d-flex align-items-center mb-2" style="gap:.75rem">
                <span class="inv-paso__num" style="background:#005484;color:#fff">1</span>
                <h2 class="mb-0">Descarga el inventario actual</h2>
            </div>
            <p class="inv-texto-2 mb-3">Obtienes un Excel con todos los productos de la empresa y su cantidad en el sistema. Úsalo para hacer el conteo físico.</p>
            <form action="{{ route('Exportar_Arqueo') }}" method="POST" class="mt-auto mb-0">
                @csrf
                <input type="hidden" name="IdEmpresa" value="{{ $idEmpresa }}">
                <button type="submit" class="btn btn-outline-primary btn-lg btn-block" {{ $primero ? '' : 'disabled' }}>
                    <i class="fas fa-file-excel mr-1"></i> Descargar Excel
                </button>
                @unless ($primero)
                    <small class="inv-ayuda d-block mt-2 text-center">Esta empresa todavía no tiene productos en inventario.</small>
                @endunless
            </form>
        </div>
    </div>

    {{-- Paso 2: subir --}}
    <div class="col-lg-7 mb-3">
        <form action="{{ route('import') }}" method="POST" enctype="multipart/form-data" id="formArqueo" class="inv-tarjeta h-100 d-flex flex-column mb-0">
            @csrf
            <div class="d-flex align-items-center mb-2" style="gap:.75rem">
                <span class="inv-paso__num" style="background:#005484;color:#fff">2</span>
                <h2 class="mb-0">Sube el conteo</h2>
            </div>
            <p class="inv-texto-2 mb-3">Cuando tengas el Excel con las cantidades contadas, súbelo aquí para actualizar el inventario.</p>

            <label class="inv-archivo mb-3" id="zonaArchivo" for="Arqueos">
                <i class="fas fa-cloud-upload-alt" id="iconoArchivo"></i>
                <strong id="nombreArchivo">Arrastra el archivo aquí o haz clic para elegirlo</strong>
                <small id="detalleArchivo">Excel (.xlsx, .xls) o .csv · máximo 5 MB</small>
                <input type="file" id="Arqueos" name="Arqueos" accept=".xlsx,.xls,.csv" required>
            </label>

            <button type="submit" class="btn btn-primary btn-lg btn-block mt-auto" id="btnImportar" disabled>
                <i class="fas fa-upload mr-1"></i> Cargar arqueo
            </button>
        </form>
    </div>
</div>

{{-- Inventario actual --}}
<h2 class="br-seccion mt-2">Inventario en el sistema</h2>
@include('Inventario.partials.filtros', [
    'accion' => route('Consultar_Arqueo_Empresa', ['id' => Crypt::encrypt($idEmpresa)]),
    'total' => $Inventario->total(),
    'campos' => [
        ['tipo' => 'texto', 'nombre' => 'texto', 'placeholder' => 'Nombre o código del producto'],
        ['tipo' => 'select', 'nombre' => 'categoria', 'etiqueta' => 'Categoría', 'todos' => 'Todas', 'opciones' => $opciones['categorias']->pluck('Nombre', 'Id')->all()],
        ['tipo' => 'select', 'nombre' => 'almacen', 'etiqueta' => 'Almacén', 'opciones' => $opciones['almacenes']->pluck('Nombre', 'Id')->all()],
        ['tipo' => 'select', 'nombre' => 'estado', 'etiqueta' => 'Stock', 'opciones' => \App\Services\Inventario\FiltrosInventario::ESTADOS],
        ['tipo' => 'orden', 'nombre' => 'orden', 'defecto' => 'producto', 'opciones' => \App\Services\Inventario\FiltrosInventario::ORDENES],
    ],
])
<div class="inv-tarjeta">

    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th scope="col">Producto</th>
                    <th scope="col">Categoría</th>
                    <th scope="col">Almacén</th>
                    <th scope="col" class="text-right">Cantidad</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($Inventario as $item)
                    <tr>
                        <td class="font-weight-bold">{{ $item->Producto }}</td>
                        <td>{{ $item->Categoria }}</td>
                        <td class="text-muted">{{ $item->Almacen }}</td>
                        <td class="text-right">
                            @if ((float) $item->Cantidades <= 0)
                                <span class="inv-estado inv-estado--critico">0</span>
                            @elseif ($item->CantidadMin !== null && (float) $item->Cantidades < (float) $item->CantidadMin)
                                <span class="inv-estado inv-estado--alerta" title="Por debajo del mínimo ({{ $item->CantidadMin + 0 }})">{{ $item->Cantidades + 0 }}</span>
                            @else
                                <strong>{{ $item->Cantidades + 0 }}</strong>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <div class="inv-vacio"><i class="fas fa-clipboard-list"></i><p>Esta empresa no tiene productos en inventario.</p></div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($Inventario->lastPage() > 1)
        <div class="inv-pie mt-3">
            <span class="inv-texto-2">Página {{ $Inventario->currentPage() }} de {{ $Inventario->lastPage() }}</span>
            <div class="inv-paginacion">{{ $Inventario->links('pagination::bootstrap-4') }}</div>
        </div>
    @endif
</div>

@push('js')
<script>
    (function () {
        var input = document.getElementById('Arqueos');
        var zona = document.getElementById('zonaArchivo');
        var boton = document.getElementById('btnImportar');
        var form = document.getElementById('formArqueo');

        function mostrarArchivo() {
            var archivo = input.files && input.files[0];
            zona.classList.toggle('tiene-archivo', !!archivo);
            document.getElementById('iconoArchivo').className = archivo ? 'fas fa-file-excel' : 'fas fa-cloud-upload-alt';
            document.getElementById('nombreArchivo').textContent = archivo ? archivo.name : 'Arrastra el archivo aquí o haz clic para elegirlo';
            document.getElementById('detalleArchivo').textContent = archivo
                ? (archivo.size / 1024 / 1024).toFixed(2) + ' MB · haz clic para cambiarlo'
                : 'Excel (.xlsx, .xls) o .csv · máximo 5 MB';
            boton.disabled = !archivo;
        }

        input.addEventListener('change', mostrarArchivo);
        ['dragenter', 'dragover'].forEach(function (ev) {
            zona.addEventListener(ev, function () { zona.classList.add('is-encima'); });
        });
        ['dragleave', 'drop'].forEach(function (ev) {
            zona.addEventListener(ev, function () { zona.classList.remove('is-encima'); });
        });

        // Mientras se carga: botón con indicador y sin doble envío
        form.addEventListener('submit', function () {
            boton.disabled = true;
            boton.innerHTML = '<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span> Cargando arqueo…';
        });

        mostrarArchivo();
    })();
</script>
@endpush
@endsection
