@extends("theme.$theme.layout")
@section('body-class', 'inv-page')
@include('Inventario.partials.estilos')

@section('content')
@if (session('success'))
    <div class="alert alert-success mb-3" role="alert">{{ session('success') }}</div>
@endif

<div class="inv-cabecera">
    <div>
        <h1 class="inv-titulo">{{ __('Categorías') }}</h1>
        <p class="inv-subtitulo">Consulta y administra las categorías de productos.</p>
    </div>
    <div class="inv-acciones">
        <a href="{{ route('Crear_Categoria') }}" class="btn btn-success"><i class="fas fa-plus"></i> {{ __('Crear') }}</a>
    </div>
</div>

@include('Inventario.partials.filtros', [
    'accion' => route('Categoria'),
    'total' => $Categoria->total(),
    'campos' => [
        ['tipo' => 'texto', 'nombre' => 'texto', 'placeholder' => 'Nombre de la categoría'],
        ['tipo' => 'select', 'nombre' => 'estado', 'etiqueta' => 'Estado', 'opciones' => ['1' => 'Activos', '2' => 'Inactivos']],
        ['tipo' => 'orden', 'nombre' => 'orden', 'defecto' => 'nombre', 'opciones' => ['nombre' => 'Nombre (A-Z)', 'nombre_desc' => 'Nombre (Z-A)', 'recientes' => 'Más recientes']],
    ],
])

<div class="inv-tabla-wrap">
    <div class="table-responsive">
        <table class="table inv-tabla">
            <thead>
                <tr>
                    <th scope="col">Id</th>
                    <th scope="col">Categoría</th>
                    <th scope="col">Estado</th>
                    <th scope="col" class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($Categoria as $item)
                    <tr>
                        <td>{{ $item->Id }}</td>
                        <td>{{ $item->Nombre }}</td>
                        <td>
                            @if ($item->Estado == 1)
                                <span class="inv-estado inv-estado--ok">Activo</span>
                            @else
                                <span class="inv-estado inv-estado--neutro">Inactivo</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('editar_Categoria', ['id' => $item->Id]) }}">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <div class="inv-vacio"><i class="fas fa-tags"></i><p>No hay categorías que coincidan con la búsqueda.</p></div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="inv-pie mt-3">
    <span class="inv-texto-2">{{ $Categoria->total() }} registros | Página {{ $Categoria->currentPage() }} de {{ $Categoria->lastPage() }}</span>
    <div class="inv-paginacion">{{ $Categoria->links('pagination::bootstrap-4') }}</div>
</div>
@endsection
