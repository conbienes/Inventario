@extends("theme.$theme.layout")

@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-primary">
                <h3 class="m-0 font-weight-bold text-white">Ventas Tarjetas de Regalo</h3>
            </div>

            <div class="card-body">
                <form method="GET" action="{{ route('BonoRegalo.informesTarjetas') }}" class="mb-4">
                    <div class="form-row align-items-end">
                        <div class="col-md-3 mb-3">
                            <label for="numero" class="form-label">Número de Recibo de Caja</label>
                            <input type="text" name="numero" value="{{ request('numero') }}" class="form-control"
                                placeholder="Ej: FAC-001">
                        </div>

                        <div class="col-md-2 mb-3">
                            <label for="cedula" class="form-label">Cédula comprador</label>
                            <input type="text" name="cedula" value="{{ request('cedula') }}" class="form-control"
                                placeholder="Ej: 1234567890">
                        </div>

                        <div class="col-md-2 mb-3">
                            <label for="fecha_inicio" class="form-label">Fecha inicio</label>
                            <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}"
                                class="form-control">
                        </div>

                        <div class="col-md-2 mb-3">
                            <label for="fecha_fin" class="form-label">Fecha fin</label>
                            <input type="date" name="fecha_fin" value="{{ request('fecha_fin') }}" class="form-control">
                        </div>

                        <div class="col-md-3 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fas fa-search mr-1"></i> Buscar
                            </button>
                            <a href="{{ route('BonoRegalo.informesTarjetas') }}" class="btn btn-outline-secondary mr-2">
                                <i class="fas fa-broom mr-1"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </form>

                <div class="alert alert-info d-flex justify-content-between align-items-center">
                    <strong>Total Recibos de Caja encontrados:</strong>
                    <span class="badge badge-primary badge-pill" style="font-size: 1.1rem;">
                        {{ $facturas->total() }}
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped" id="dataTable" width="100%"
                        cellspacing="0">
                        <thead class="bg-gradient-primary text-white">
                            <tr>
                                <th>Recibo de Caja</th>
                                <th>Tarjeta</th>
                                <th>Fecha Compra</th>
                                <th>Cédula</th>
                                <th class="text-right">Valor Tarjeta</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($facturas as $factura)
                                <tr>
                                    <td class="font-weight-bold">{{ $factura->factura }}</td>
                                    <td>{{ $factura->tarjetas }}</td>
                                    <td>{{ \Carbon\Carbon::parse($factura->fecha_min)->isoFormat('D MMM YYYY') }}</td>
                                    <td>{{ $factura->cedula_min }}</td>
                                    <td class="text-right text-success font-weight-bold">
                                        ${{ number_format($factura->valor_total, 2) }}
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('BonoRegalo.printFactura', $factura->id_ref) }}" target="_blank"
                                            class="btn btn-sm btn-primary">
                                            <i class="fas fa-print"></i> Imprimir
                                        </a>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="fas fa-exclamation-circle fa-2x mb-3 text-muted"></i>
                                        <p class="h5 text-muted">No se encontraron registros con los filtros aplicados</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($facturas->count() > 0)
                            <tfoot class="bg-light">
                                <tr>
                                    <td colspan="4" class="text-right font-weight-bold">Totales:</td>
                                    <td class="text-right font-weight-bold text-primary">
                                        ${{ number_format($facturas->sum('valor_total'), 2) }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12">
                        {{ $facturas->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .card-header {
            border-radius: 0.35rem 0.35rem 0 0 !important;
        }

        .table th {
            border-top: none;
        }

        .badge-pill {
            padding: 0.5em 0.8em;
        }

        .bg-gradient-primary {
            background: linear-gradient(87deg, #5e72e4 0, #825ee4 100%) !important;
        }

        .pagination {
            justify-content: center;
        }

        .page-item.active .page-link {
            background-color: #5e72e4;
            border-color: #5e72e4;
        }

        .page-link {
            color: #5e72e4;
        }

        th a {
            color: white;
            text-decoration: none;
            display: block;
        }

        th a:hover {
            color: #f8f9fa;
        }
    </style>
@endsection
