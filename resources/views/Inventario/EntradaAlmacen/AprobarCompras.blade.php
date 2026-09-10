@extends("theme.$theme.layout")

@section('content')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <!-- Modal -->
    <div id="myModal" class="modal fade">
        <div class="modal-header ">
            <div class="modal-content">
                <div class="modal-header">
                    <h5> <strong>APROBAR PRODUCTOS RECIBIDOS</strong></h5>
                </div>
                <div class="card-body">
                    <table class="table-striped table-bordered">
                        <form action="{{ route('AprobarItems') }}" method="POST">
                            @csrf
                            <div ALIGN="center" class="form-group">
                                <label for="selec-comunidad">Estado </label>
                                <select class="form-select" name="Estado" id="Estado" required>
                                    <option></option>
                                    <option value="1">Parcial</option>
                                    <option value="3">Cerrar entrega parcial</option>
                                    <option value="2">Pedido Completo</option>
                                </select>
                            </div>

                            <br>
                            <thead>
                                <tr ALIGN="center">
                                    <th width="450"scope="col">Producto</th>
                                    <th width="200"scope="col">Cantidad Solicitada</th>
                                    <th width="200"scope="col">Cantidades Recibidas</th>
                                    <th width="650"scope="col">Comentario</th>
                                    <th width="200"scope="col">Cantidad Recibida </th>
                                    <th width="550"scope="col">Comentario</th>
                                </tr>
                            </thead>

                            @foreach ($SoliPendi as $item)
                                @if ($item->Completas == 0)
                                    <tr ALIGN="center">
                                        <td width="80">
                                            <font size=2><input type="hidden" value="{{ $item->Id }}" name="IdItem[]">
                                                {{ $item->Producto }}"</font>
                                        </td>
                                        <td width="80">
                                            <font size=3>{{ $item->Cantidades }}</font>
                                        </td>
                                        <td width="80">
                                            <font size=3>{{ $item->Aprobadas }}</font>
                                        </td>
                                        <td width="80">
                                            <font size=3>{{ $item->Comentario }}</font>
                                        </td>
                                        <td><input type="number" type="hidden" name="Catidad[]" id="Catidad"
                                                style="width : 50px; heigth : 10px" autocomplete="off"></td>
                                        <td>
                                            <textarea name="Comentario[]" id="Comentario" maxlength="150" cols="80" rows="1" autocomplete="off"></textarea>
                                        </td>
                                    </tr>
                                    <input type="hidden" value="{{ $item->RegistroFactura }}" name="RegistroFactura">
                                    </font>
                                    </td>
                                    <input type="hidden" value="{{ $item->Inventario }}" name="Inventario[]"></font>
                                    </td>
                                @endif
                            @endforeach

                            <table class="table" style="width : 50px; heigth : 10px" ALIGN="center">
                                <tr>
                                    <td>
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-success">{{ __('Aprobar') }}</button>
                                        </div>
                                    </td>
                        </form>

                        <td>
                            <div class="form-group ">
                                <a class="btn btn-danger"
                                    href="{{ route('SolicPendFacturas', ['id' => Crypt::encrypt($div->id)]) }}">{{ __('Cancelar') }}
                                </a>
                            </div>
                        </td>
                        </tr>
                    </table>


                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#myModal').modal({
                backdrop: 'static',
                keyboard: false
            }).modal('show')
        });
    </script>

    <script>
        $(function() {
            $("#Estado").change(function() {
                if ($(this).val() === "2") {
                    $("input").prop('hidden', true);
                    $("textarea").prop('hidden', true);
                }
            });
        });
    </script>
    <script>
        $(function() {
            $("#Estado").change(function() {
                if ($(this).val() === "1") {
                    $("textarea").prop('hidden', false);
                    $("input").prop('hidden', false);
                }
            });
        });
    </script>
@endsection
