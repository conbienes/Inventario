@extends("theme.$theme.layout")
@section('body-class', 'inv-page')
@include('Inventario.partials.estilos')

@section('content')
@if (session('success'))
    <div class="alert alert-success mb-3" role="alert">{{ session('success') }}</div>
@endif
@if (session('succes'))
    <div class="alert alert-success mb-3" role="alert">{{ session('succes') }}</div>
@endif

<div class="inv-cabecera">
    <div>
        <h1 class="inv-titulo">{{ __('Unidades de medida') }}</h1>
        <p class="inv-subtitulo">Consulta y administra las unidades de medida.</p>
    </div>
    <div class="inv-acciones">
        <a href="{{ route('Crear_Medida') }}" class="btn btn-success"><i class="fas fa-plus"></i> {{ __('Crear') }}</a>
    </div>
</div>

@include('Inventario.partials.filtros', [
    'accion' => route('UnidMedida'),
    'total' => $UnidadesM->total(),
    'campos' => [
        ['tipo' => 'texto', 'nombre' => 'texto', 'placeholder' => 'Nombre o abreviatura'],
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
                    <th scope="col">Unidad</th>
                    <th scope="col">Abreviatura</th>
                    <th scope="col">Estado</th>
                    <th scope="col" class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($UnidadesM as $item)
                    <tr>
                        <td>{{ $item->Id }}</td>
                        <td>{{ $item->Nombre }}</td>
                        <td>{{ $item->Abreviatura }}</td>
                        <td>
                            @if ($item->Estado == 1)
                                <span class="inv-estado inv-estado--ok">Activo</span>
                            @else
                                <span class="inv-estado inv-estado--neutro">Inactivo</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ route('editar_UnidMedida', ['id' => $item->Id]) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="inv-vacio"><i class="fas fa-balance-scale"></i><p>No hay unidades que coincidan con la búsqueda.</p></div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="inv-pie mt-3">
    <span class="inv-texto-2">{{ $UnidadesM->total() }} registros | Página {{ $UnidadesM->currentPage() }} de {{ $UnidadesM->lastPage() }}</span>
    <div class="inv-paginacion">{{ $UnidadesM->links('pagination::bootstrap-4') }}</div>
</div>
@endsection
