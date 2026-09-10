@extends("theme.$theme.layout")

@section('content')
    <div class="container">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title mb-0">{{ __('ALERTASTOCK') }}</h3>
            </div>

            <div class="card-body">
                @forelse ($Division as $item)
                    <div class="row justify-content-center mb-3">
                        <div class="col-12 col-md-8 col-lg-6">
                            <div class="info-box shadow-sm" style="border-radius: 0.5rem;">
                                <span class="info-box-icon d-flex align-items-center justify-content-center"
                                    style="border-top-left-radius: .5rem; border-bottom-left-radius: .5rem;">
                                    <img src="{{ asset("assets/$theme/dist/img/9.png") }}" alt="Alerta stock"
                                        style="max-height: 48px; width:auto;" />
                                </span>
                                <a href="{{ route('AlertaStock', ['id' => Crypt::encrypt($item->id)]) }}"
                                    class="text-dark text-decoration-none flex-grow-1">
                                    <div class="info-box-content">
                                        <span class="info-box-text fw-semibold">{{ $item->nombre }}</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">
                        {{ __('No hay divisiones para mostrar.') }}
                    </div>
                @endforelse

                <div class="text-center mt-3">
                    <a class="btn btn-primary" href="{{ route('InicioInventario') }}">
                        {{ __('Cancelar') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
