@extends("theme.$theme.layout")
@section('body-class', 'inv-page')
@include('Inventario.partials.estilos')

@section('content')
@if (session('success'))
    <div class="alert alert-success mb-3" role="alert">{{ session('success') }}</div>
@endif

<div class="inv-cabecera">
    <div>
        <h1 class="inv-titulo">{{ __('Productos') }}</h1>
        <p class="inv-subtitulo">Consulta y administra el catálogo de productos.</p>
    </div>
    <div class="inv-acciones">
        <a href="{{ route('Crear_Producto') }}" class="btn btn-success"><i class="fas fa-plus"></i> {{ __('Crear') }}</a>
    </div>
</div>

@include('Inventario.partials.filtros', [
    'accion' => route('Producto'),
    'total' => $Producto->total(),
    'campos' => [
        ['tipo' => 'texto', 'nombre' => 'texto', 'placeholder' => 'Nombre o código'],
        ['tipo' => 'select', 'nombre' => 'unidad', 'etiqueta' => 'Unidad de medida', 'todos' => 'Todas', 'opciones' => $unidades],
        ['tipo' => 'select', 'nombre' => 'estado', 'etiqueta' => 'Estado', 'opciones' => ['1' => 'Activos', '2' => 'Inactivos']],
        ['tipo' => 'orden', 'nombre' => 'orden', 'defecto' => 'nombre', 'opciones' => ['nombre' => 'Nombre (A-Z)', 'nombre_desc' => 'Nombre (Z-A)', 'recientes' => 'Más recientes', 'codigo' => 'Código']],
    ],
])

<div class="inv-tabla-wrap">
    <div class="table-responsive">
        <table class="table inv-tabla">
            <thead>
                <tr>
                    <th scope="col">Código</th>
                    <th scope="col">Producto</th>
                    <th scope="col">Unidad de medida</th>
                    <th scope="col">Estado</th>
                    <th scope="col" class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($Producto as $item)
                    <tr>
                        <td>{{ $item->Codigo }}</td>
                        <td>{{ $item->Nombre }}</td>
                        <td>{{ $item->Medida }}</td>
                        <td>
                            @if ($item->Estado == 1)
                                <span class="inv-estado inv-estado--ok">Activo</span>
                            @else
                                <span class="inv-estado inv-estado--neutro">Inactivo</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ route('editar_Producto', ['id' => $item->Id]) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="inv-vacio"><i class="fas fa-box-open"></i><p>No hay productos que coincidan con la búsqueda.</p></div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="inv-pie mt-3">
    <span class="inv-texto-2">{{ $Producto->total() }} registros | Página {{ $Producto->currentPage() }} de {{ $Producto->lastPage() }}</span>
    <div class="inv-paginacion">{{ $Producto->links('pagination::bootstrap-4') }}</div>
</div>
@endsection
