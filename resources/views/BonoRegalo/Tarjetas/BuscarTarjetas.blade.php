@extends("theme.$theme.layout")

@section('content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-11">

                {{-- ====== TODOS LOS TIPOS DE ALERTAS ====== --}}
                @if(session('success'))
                    <div class="alert alert-success border-0 shadow-sm rounded-3 alert-dismissible fade show d-flex align-items-center" role="alert">
                        <i class="fas fa-check-circle fa-lg me-3 text-success"></i>
                        <div>{!! session('success') !!}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning border-0 shadow-sm rounded-3 alert-dismissible fade show d-flex align-items-center" role="alert">
                        <i class="fas fa-exclamation-triangle fa-lg me-3 text-warning"></i>
                        <div>{!! session('warning') !!}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger border-0 shadow-sm rounded-3 alert-dismissible fade show d-flex align-items-center" role="alert">
                        <i class="fas fa-times-circle fa-lg me-3 text-danger"></i>
                        <div>{!! session('error') !!}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('info'))
                    <div class="alert alert-info border-0 shadow-sm rounded-3 alert-dismissible fade show d-flex align-items-center" role="alert">
                        <i class="fas fa-info-circle fa-lg me-3 text-info"></i>
                        <div>{!! session('info') !!}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger border-0 shadow-sm rounded-3 alert-dismissible fade show" role="alert">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-exclamation-triangle fa-lg me-2 text-danger"></i>
                            <h6 class="mb-0 fw-bold">Por favor, corrige los siguientes errores:</h6>
                        </div>
                        <ul class="mb-0 mt-1">
                            @foreach ($errors->all() as $error)
                                <li>{!! $error !!}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- Tarjeta Principal --}}
                <div class="card border-0 shadow rounded-4 overflow-hidden">

                    {{-- Cabecera --}}
                    <div class="card-header bg-primary bg-gradient text-white p-4 d-flex flex-column flex-md-row justify-content-between align-items-center border-0">
                        <div class="d-flex align-items-center mb-3 mb-md-0">
                            <div class="bg-white bg-opacity-25 rounded-circle p-3 me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-gift fa-xl"></i>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold">Tarjetas Bono Regalo</h4>
                                <small class="text-white-50">Gestión e importación de bonos</small>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('BonoRegalo.IndexTarjeta') }}" class="btn btn-light rounded-pill px-3 shadow-sm hover-elevate">
                                <i class="fas fa-plus-circle me-1 text-primary"></i> Nueva Tarjeta
                            </a>
                        </div>
                    </div>

                    <div class="card-body p-4 p-md-5 bg-light">
                        {{-- SECCIÓN DE IMPORTACIÓN --}}
                        <div class="card border border-2 border-primary border-opacity-25 rounded-3 mb-4 border-dashed" style="border-style: dashed !important;">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-cloud-upload-alt fa-2x text-primary me-3"></i>
                                    <div>
                                        <h5 class="mb-0 fw-bold">Importar Tarjetas Masivas</h5>
                                        <p class="text-muted small mb-0">Sube tu archivo .xlsx o .csv con los datos actualizados.</p>
                                    </div>
                                </div>
                                <form action="{{ route('BonoRegalo.ImportarTarjeta') }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-center" id="importForm">
                                    @csrf
                                    <div class="col-md-7">
                                        <input type="file" name="archivo" id="archivo"
                                            class="form-control form-control-lg bg-white shadow-sm @error('archivo') is-invalid @enderror"
                                            accept=".xlsx,.xls,.csv" required>
                                        @error('archivo')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-5 d-flex gap-2">
                                        <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm" id="importBtn">
                                            <i class="fas fa-upload me-2"></i> Subir
                                        </button>                                      
                                    </div>
                                </form>
                                <!-- Barra de progreso -->
                                <div id="progress-container" class="d-none mt-3">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span class="text-muted">Procesando archivo...</span>
                                        <span class="fw-bold text-primary" id="progress-text">0%</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" id="progress-bar" style="width: 0%;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- SECCIÓN DE FILTROS --}}
                        <div class="bg-white p-4 rounded-3 shadow-sm mb-4">
                            <form method="GET" action="{{ route('BonoRegalo.BuscarTarjeta') }}" class="row g-3 align-items-end">
                                <div class="col-md-2">
                                    <label class="form-label text-muted small fw-bold text-uppercase">N° de Tarjeta</label>
                                    <input type="text" name="numero" value="{{ request('numero') }}" class="form-control" placeholder="Ej: 4198...">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label text-muted small fw-bold text-uppercase">NIT Comprador</label>
                                    <input type="text" name="cedula" value="{{ request('cedula') }}" class="form-control" placeholder="Ej: 9013...">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label text-muted small fw-bold text-uppercase">Desde</label>
                                    <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}" class="form-control">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label text-muted small fw-bold text-uppercase">Hasta</label>
                                    <input type="date" name="fecha_fin" value="{{ request('fecha_fin') }}" class="form-control">
                                </div>
                                <div class="col-md-4 d-flex gap-2">
                                    <button type="submit" class="btn btn-dark flex-fill shadow-sm">
                                        <i class="fas fa-search me-2"></i> Buscar
                                    </button>
                                    <a href="{{ route('BonoRegalo.BuscarTarjeta') }}" class="btn btn-light border text-muted flex-fill shadow-sm" title="Limpiar filtros">
                                        <i class="fas fa-eraser"></i>
                                    </a>
                                    <a href="{{ route('BonoRegalo.ExportarTarjeta', request()->all()) }}" class="btn btn-success flex-fill shadow-sm" title="Exportar a Excel">
                                        <i class="fas fa-file-excel"></i>
                                    </a>
                                </div>
                            </form>
                        </div>

                        {{-- TABLA DE RESULTADOS --}}
                        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light text-muted small">
                                        <tr>
                                            <th class="text-center text-uppercase fw-bold py-3" width="80">ID</th>
                                            <th class="text-uppercase fw-bold py-3">NIT</th>
                                            <th class="text-uppercase fw-bold py-3">Número</th>
                                            <th class="text-end text-uppercase fw-bold py-3" width="150">Valor</th>
                                            <th class="text-center text-uppercase fw-bold py-3" width="150">Creación</th>
                                            <th class="text-center text-uppercase fw-bold py-3" width="120">Estado</th>
                                            <th class="text-center text-uppercase fw-bold py-3" width="100">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        @forelse ($Tarjetas as $Item)
                                            <tr>
                                                <td class="text-center text-muted">#{{ $Item->id }}</td>
                                                <td class="fw-medium">{{ $Item->nit }}</td>
                                                <td>
                                                    <span class="badge bg-light text-secondary border font-monospace px-2 py-1 fs-6">
                                                        <i class="far fa-credit-card me-1"></i> {{ $Item->numero }}
                                                    </span>
                                                </td>
                                                <td class="text-end text-success fw-bold">
                                                    ${{ number_format($Item->valor, 0, ',', '.') }}
                                                </td>
                                                <td class="text-center text-muted small">
                                                    {{ $Item->created_at->format('d M Y') }}
                                                </td>
                                                <td class="text-center">
                                                    @if($Item->estado == 'activa')
                                                        <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success px-3 py-1">Activa</span>
                                                    @else
                                                        <span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary border border-secondary px-3 py-1">{{ ucfirst($Item->estado) }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('BonoRegalo.editarTarjeta', $Item->id) }}" class="btn btn-sm btn-light border text-primary hover-shadow transition-all" title="Editar">
                                                        <i class="fas fa-pen"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-5 bg-white">
                                                    <div class="py-4">
                                                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                                            <i class="fas fa-inbox fa-2x text-muted opacity-50"></i>
                                                        </div>
                                                        <h5 class="fw-bold text-dark">No hay tarjetas registradas</h5>
                                                        <p class="text-muted">Ajusta los filtros de búsqueda o importa nuevas tarjetas para comenzar.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Paginación --}}
                    @if ($Tarjetas->hasPages())
                        <div class="card-footer bg-white p-4 border-top">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                                <div class="text-muted small mb-3 mb-md-0">
                                    Mostrando resultados <span class="fw-bold text-dark">{{ $Tarjetas->firstItem() }}</span> al
                                    <span class="fw-bold text-dark">{{ $Tarjetas->lastItem() }}</span> de un total de
                                    <span class="fw-bold text-dark">{{ $Tarjetas->total() }}</span>
                                </div>
                                <div class="m-0">
                                    {{ $Tarjetas->appends(request()->query())->links('pagination::bootstrap-4') }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<style>
    .hover-elevate {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .hover-elevate:hover {
        transform: translateY(-2px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
    .hover-shadow:hover {
        background-color: #f8f9fa;
        box-shadow: 0 .125rem .25rem rgba(0,0,0,.075);
    }
    .border-dashed {
        border-style: dashed !important;
    }
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Auto-cierre de alertas
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getInstance(alert);
            if (bsAlert) {
                bsAlert.close();
            }
        }, 8000); // 8 segundos para que alcances a leer mensajes largos
    });

    // Mostrar info del archivo
    const fileInput = document.getElementById('archivo');
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                const fileName = this.files[0].name;
                const fileSize = (this.files[0].size / 1024).toFixed(2);
                
                // Crear un pequeño indicador visual
                const parentDiv = this.closest('.card-body');
                if (parentDiv) {
                    let existingAlert = parentDiv.querySelector('.file-info-alert');
                    if (!existingAlert) {
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-success py-2 px-3 mt-2 mb-0 border-0 d-inline-block w-100 shadow-sm file-info-alert';
                        alertDiv.innerHTML = `
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>¡Archivo listo!</strong> ${fileName} <span class="badge bg-white text-success ms-2">${fileSize} KB</span>
                        `;
                        this.parentNode.appendChild(alertDiv);
                    } else {
                        existingAlert.innerHTML = `
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>¡Archivo listo!</strong> ${fileName} <span class="badge bg-white text-success ms-2">${fileSize} KB</span>
                        `;
                    }
                }
            }
        });
    }

    // Barra de progreso para el envío del formulario
    const importForm = document.getElementById('importForm');
    const progressContainer = document.getElementById('progress-container');
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    const importBtn = document.getElementById('importBtn');

    if (importForm) {
        importForm.addEventListener('submit', function(e) {
            const fileInput = document.getElementById('archivo');
            if (fileInput && fileInput.files.length > 0) {
                progressContainer.classList.remove('d-none');
                let progress = 0;
                const interval = setInterval(() => {
                    progress += Math.random() * 8;
                    if (progress > 95) progress = 95;
                    progressBar.style.width = progress + '%';
                    progressText.textContent = Math.round(progress) + '%';
                }, 200);

                importBtn.disabled = true;
                importBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Procesando...';

                setTimeout(() => {
                    clearInterval(interval);
                    progressBar.style.width = '100%';
                    progressText.textContent = '100%';
                }, 5000);
            }
        });
    }
});
</script>
@endpush