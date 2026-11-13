{{--
    Campos comunes de alta y edición.
    Variables: $empleado (Usuario o null), $esEdicion (bool), $TipoEmpleado, $area, $division, $estado, $sexo.
    Los atributos name no deben cambiar: los espera RegEmpleadoController (guardar/actualizar).
--}}
@php
    $datos = $empleado ?? null;
    $valor = fn (string $campo, ?string $columna = null) => old($campo, $datos ? ($datos->{$columna ?? $campo} ?? '') : '');
    $seleccionado = fn (string $campo, $id) => (string) $valor($campo) === (string) $id ? 'selected' : '';
    $edicion = (bool) ($esEdicion ?? false);
@endphp

<h5 class="emp-seccion">Información personal</h5>
<div class="form-row">
    <div class="form-group col-12 col-md-6 col-lg-4">
        <label for="cedula" class="font-weight-bold">Cédula</label>
        <input type="number" class="form-control" id="cedula" name="cedula" value="{{ $valor('cedula') }}"
            placeholder="Ingrese la cédula" required>
    </div>
    <div class="form-group col-12 col-md-6 col-lg-4">
        <label for="nombre" class="font-weight-bold">Nombre</label>
        <input type="text" class="form-control" id="nombre" name="nombre" value="{{ $valor('nombre') }}"
            placeholder="Ingrese el nombre" required>
    </div>
    <div class="form-group col-12 col-md-6 col-lg-4">
        <label for="apellidos" class="font-weight-bold">Apellidos</label>
        <input type="text" class="form-control" id="apellidos" name="apellidos" value="{{ $valor('apellidos') }}"
            placeholder="Ingrese los apellidos" required>
    </div>
    <div class="form-group col-12 col-md-6">
        <label for="email" class="font-weight-bold">Correo electrónico</label>
        <input type="email" class="form-control" id="email" name="email" value="{{ $valor('email', 'Correo') }}"
            placeholder="ejemplo@correo.com" autocomplete="off">
    </div>
    <div class="form-group col-12 col-md-6">
        <label for="password" class="font-weight-bold">Contraseña</label>
        <input type="password" class="form-control" id="password" name="password" autocomplete="new-password"
            placeholder="{{ $edicion ? 'Dejar en blanco para mantener la actual' : 'Contraseña' }}"
            @unless ($edicion) required @endunless>
        <small class="emp-ayuda">
            @if ($edicion)
                Complete solo si desea cambiarla. Mínimo 8 caracteres, con una mayúscula, un número y un carácter especial.
            @else
                Mínimo 8 caracteres, con una mayúscula, un número y un carácter especial.
            @endif
        </small>
    </div>
</div>

<h5 class="emp-seccion">Información laboral</h5>
<div class="form-row">
    <div class="form-group col-12 col-md-6 col-lg-4">
        <label for="id_tipoempleado" class="font-weight-bold">Tipo de empleado</label>
        <select class="custom-select" id="id_tipoempleado" name="id_tipoempleado" required>
            @foreach ($TipoEmpleado as $item)
                <option value="{{ $item->id }}" {{ $seleccionado('id_tipoempleado', $item->id) }}>{{ $item->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-12 col-md-6 col-lg-4">
        <label for="id_area" class="font-weight-bold">Área</label>
        <select class="custom-select" id="id_area" name="id_area" required>
            @foreach ($area as $item)
                <option value="{{ $item->id }}" {{ $seleccionado('id_area', $item->id) }}>{{ $item->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-12 col-md-6 col-lg-4">
        <label for="id_div" class="font-weight-bold">División</label>
        <select class="custom-select" id="id_div" name="id_div" required>
            @foreach ($division as $item)
                <option value="{{ $item->id }}" {{ $seleccionado('id_div', $item->id) }}>{{ $item->nombre }}</option>
            @endforeach
        </select>
    </div>
</div>

<h5 class="emp-seccion">Información adicional</h5>
<div class="form-row">
    <div class="form-group col-12 col-md-6">
        <label for="id_estado" class="font-weight-bold">Estado</label>
        <select class="custom-select" id="id_estado" name="id_estado" required>
            @foreach ($estado as $item)
                <option value="{{ $item->id }}" {{ $seleccionado('id_estado', $item->id) }}>{{ $item->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-12 col-md-6">
        <label for="id_sexo" class="font-weight-bold">Sexo</label>
        <select class="custom-select" id="id_sexo" name="id_sexo" required>
            @foreach ($sexo as $item)
                <option value="{{ $item->id }}" {{ $seleccionado('id_sexo', $item->id) }}>{{ $item->nombre }}</option>
            @endforeach
        </select>
    </div>
</div>
