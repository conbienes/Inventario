@extends("theme.$theme.layout")

@section('content')

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">

<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.0.0-alpha1/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>

  <!-- Modal -->

  <div class="card card-primary">
    <div class="card-header">
      <h3 class="card-title">{{__('ENTRADA PEDIDO DE COMPRA')}}</h3>
    </div>
      <div class="modal-content"  >
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('AñadirFactura') }}">
            @csrf
        <div class="card-body">
            @csrf
                <div class="form-group">
                  @foreach ($Empleado as $item)
                  <div class="form-group">
                    <label for="Nombre">{{__('Almacenista')}}</label>
                    <input type="text" readonly="readonly" class="form-control" name="Solicitante" autocomplete="off" autofocus value="{{$item->nombre}} {{$item->apellidos}}">
                    </div>
                  @endforeach

                  @foreach ($Division as $item)
                    <input type="hidden"  class="form-control" name="Division" autocomplete="off" autofocus value="{{$item->id}}">
                  @endforeach

                  <label>Proveedor</label>
                  <select class="Proveedor " required style="width: 100%" name="Proveedor" id="Proveedor" autocomplete="off" ></select> <br><br>

              <div class="form-group">
                <label for="Direccion">{{ __('# Remision') }}</label>
                <input type="text" class="form-control" name="NFactura" autocomplete="off" autofocus required>
                </div>
              <div class="form-group">
                <label for="Direccion">{{ __('Precio Total') }}</label>
                <input type="number" class="form-control" name="PrecioT" autocomplete="off" autofocus required>
                </div>
              <label for="text">{{ __('# Orden de Pedido') }}</label>
                <input type="text" class="form-control" name="OPedido" autocomplete="off" autofocus required>
                </div>
                <div class="form-group">
                  <label for="text">{{ __('Fecha de Ingreso') }}</label>
                  <input type="date" class="form-control" name="FechaI" autocomplete="off" autofocus required>
                  </div>
          <table class="table" >
            <tr ALIGN="center">
             <td >
               <div class="form-group" >
               <button type="submit" class="btn btn-success">{{ __('Añadir Items') }}</button>
               </div>
            </td>
                 <td>
                   <div class="form-group ">
                    @foreach ($Division as $item)
                    <a class="btn btn-danger" href="{{route('Vista_EntAlmacen',['id'=>Crypt::encrypt($item->id)])}}" >{{ __('Cancelar') }} </a>
                    @endforeach
                   </div>
                 </td>
           </tr>
         </table>
        </form>
      </div>
    </div>




<script type="text/javascript">
    $('.Proveedor').select2({
      theme: "classic",
      language: {
              noResults: function() {return "Proveedor no encontrado"; },
              searching: function(){ return "Buscando......." }
          },
        ajax: {
            url: '/Ajax/Proveedor',
            dataType: 'json',
            delay: 250,
            processResults: function (data) {
                return {
                    results: $.map(data, function (item) {
                        return {
                            text: 'NIT: '+ item.Nit + ' | PROVEEDOR: ' + item.Nombre,
                            id: item.Id
                        }
                    })
                };
            },
            cache: true
        }
    });
  </script>

@endsection

<style>
  .select2-container--material {
   width: 100% !important;
  font-size: 80%  }
   .select2-container--material .select2-selection--single {
     background-color: transparent;
     border: none;
     border-bottom: 0.5px solid #ced4da;
     border-radius: 0;
     box-shadow: none;
     box-sizing: content-box;
     height: auto;
     margin: 0;
     outline: none;
     transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out; }
     .select2-container--material .select2-selection--single .select2-selection__placeholder {
       color: #999; }
   .select2-container--material .select2-search--dropdown .select2-search__field {
     border: none;
     border-bottom: 0.5px solid #ced4da;
     border-radius: 0;
     outline: none; }
   .select2-container--material .select2-results__option--highlighted[aria-selected] {
     background-color: #3498DB ;
     color: rgb(0, 0, 0); }
 </style>
