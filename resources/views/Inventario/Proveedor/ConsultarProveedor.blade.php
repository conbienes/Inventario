@extends("theme.$theme.layout")
@section('body-class', 'inv-page')
@include('Inventario.partials.estilos')

@section('content')
@if (session('success'))
    <div class="alert alert-success mb-3" role="alert">{{ session('success') }}</div>
@endif

<div class="inv-cabecera">
    <div>
        <h1 class="inv-titulo">{{ __('Proveedores') }}</h1>
        <p class="inv-subtitulo">Consulta y administra los proveedores.</p>
    </div>
    <div class="inv-acciones">
        <a href="{{ route('Crear_Proveedor') }}" class="btn btn-success"><i class="fas fa-plus"></i> {{ __('Crear') }}</a>
    </div>
</div>

@include('Inventario.partials.filtros', [
    'accion' => route('Proveedor'),
    'total' => $Proveedor->total(),
    'campos' => [
        ['tipo' => 'texto', 'nombre' => 'texto', 'placeholder' => 'Nombre, NIT, correo o teléfono'],
        ['tipo' => 'orden', 'nombre' => 'orden', 'defecto' => 'nombre', 'opciones' => ['nombre' => 'Nombre (A-Z)', 'nombre_desc' => 'Nombre (Z-A)', 'recientes' => 'Más recientes']],
    ],
])

<div class="inv-tabla-wrap">
    <div class="table-responsive">
        <table class="table inv-tabla">
            <thead>
                <tr>
                    <th scope="col">NIT</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Teléfono</th>
                    <th scope="col">Correo</th>
                    <th scope="col" class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($Proveedor as $item)
                    <tr>
                        <td>{{ $item->Nit }}</td>
                        <td>{{ $item->Nombre }}</td>
                        <td>{{ $item->Telefono ?: '—' }}</td>
                        <td>{{ $item->Correo ?: '—' }}</td>
                        <td class="text-right">
                            <a href="{{ route('editar_Proveedor', ['id' => $item->Id]) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="inv-vacio"><i class="fas fa-truck"></i><p>No hay proveedores que coincidan con la búsqueda.</p></div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="inv-pie mt-3">
    <span class="inv-texto-2">{{ $Proveedor->total() }} registros | Página {{ $Proveedor->currentPage() }} de {{ $Proveedor->lastPage() }}</span>
    <div class="inv-paginacion">{{ $Proveedor->links('pagination::bootstrap-4') }}</div>
</div>
@endsection
