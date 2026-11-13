{{-- Selector de empresa (división) para Inventario, Entradas de almacén, Arqueo y Alertas de stock.
     Uso: @include('Inventario.partials.selector-division', ['ruta' => 'Nombre_De_Ruta'])
     La ruta debe recibir el id cifrado de la división como parámetro {id}. --}}
@php
    $divisiones = divisionesInventario();
    $actual = divisionInventarioActual();

    // Un color por empresa para identificarla de un vistazo (se repite si hay más de 6)
    $paleta = ['#005484', '#C2410C', '#7C3AED', '#15803D', '#BE185D', '#0E7490'];
@endphp

@if ($divisiones->count() > 1)
    <nav class="inv-empresas" aria-label="Cambiar de empresa">
        <span class="inv-empresas__etiqueta"><i class="fas fa-building mr-1"></i> Empresa</span>
        <div class="inv-empresas__lista">
            @foreach ($divisiones as $d)
                @php
                    $activa = (int) $d->id === $actual;
                    $color = $paleta[($d->id - 1) % count($paleta)];
                @endphp
                <a href="{{ route($ruta, ['id' => Crypt::encrypt($d->id)]) }}"
                    class="inv-empresas__op {{ $activa ? 'is-activa' : '' }}" style="--empresa: {{ $color }}"
                    @if ($activa) aria-current="page" @endif>
                    @if ($activa)
                        <i class="fas fa-check-circle mr-2"></i>
                    @else
                        <span class="inv-empresas__punto" aria-hidden="true"></span>
                    @endif
                    {{ $d->nombre }}
                </a>
            @endforeach
        </div>
    </nav>

    @once
        @push('css')
            <style>
                .inv-empresas {
                    display: flex; align-items: center; gap: .75rem;
                    background: #fff; border: 1px solid #E2E8F0; border-radius: 14px;
                    padding: .5rem .6rem .5rem 1rem; margin-bottom: 1.25rem;
                    box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
                }
                .inv-empresas__etiqueta {
                    flex: none; font-size: .8rem; font-weight: 700; letter-spacing: .06em;
                    text-transform: uppercase; color: #4B5563;
                }
                .inv-empresas__lista { display: flex; flex-wrap: wrap; gap: .4rem; flex: 1 1 auto; min-width: 0; }
                .inv-empresas__op {
                    flex: none; display: inline-flex; align-items: center;
                    padding: .5rem 1rem; border-radius: 10px; white-space: nowrap;
                    font-weight: 600; font-size: .95rem; color: #1F2937; text-decoration: none !important;
                    background: #F1F5F9; border: 1px solid transparent;
                    transition: background-color .15s ease, color .15s ease, border-color .15s ease;
                }
                .inv-empresas__op {
                    background: color-mix(in srgb, var(--empresa) 8%, #fff);
                    border-color: color-mix(in srgb, var(--empresa) 22%, #fff);
                }
                .inv-empresas__punto {
                    width: 10px; height: 10px; border-radius: 50%; margin-right: .55rem;
                    background: var(--empresa); flex: none;
                }
                .inv-empresas__op:hover {
                    background: color-mix(in srgb, var(--empresa) 16%, #fff);
                    border-color: var(--empresa); color: #1F2937;
                }
                .inv-empresas__op.is-activa {
                    background: var(--empresa); color: #fff; border-color: var(--empresa);
                    box-shadow: 0 4px 12px color-mix(in srgb, var(--empresa) 35%, transparent);
                }
                .inv-empresas__op:focus-visible { outline: 3px solid rgba(0, 84, 132, .35); outline-offset: 2px; }
                @media (max-width: 575.98px) {
                    .inv-empresas { flex-direction: column; align-items: stretch; gap: .4rem; padding: .6rem; }
                    .inv-empresas__lista { flex-direction: column; }
                    .inv-empresas__op { white-space: normal; justify-content: flex-start; }
                }
            </style>
        @endpush
    @endonce
@endif
