@extends("theme.$theme.layout")

@section('content')

<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.0.0-alpha1/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
<link rel="stylesheet" href="/path/to/select2.css">
<link rel="stylesheet" href="/path/to/select2-bootstrap4.min.css">
    
  <!-- Modal -->
  <div class="modal fade" id="myModal"  >
    <div class="modal-dialog " >
      <div class="modal-content"  >
        <div class="modal-header"  >
            @foreach ($Inventario as $item)     
            <table>
               <tr>
                   <td>
                    <h5 >{{$item->Producto}} <br> </h5>
                   </td>
               </tr>
               <tr ALIGN="right">
                   <td>
                    <h6><strong>Cantidad actual: </strong>{{$item->Cantidades}}</h6>
                   </td>
               </tr>
           </table>                                                     
            @endforeach      
        </div>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('Guardar_Movimientos') }}">
            @csrf 
        <div class="card-body">
           
       
        @foreach ($Inventario as $item)
        <div class="form-group">
            <input type="hidden" class="form-control" name="Id" value={{$item->Id}} >
          </div>                                  
          <div class="form-group">
            <label for="Nombre">{{ __('Area Solicitante') }}</label><br>
            <select class="Area " name="Area" id="Area"></select>
          </div>     
          <div class="form-group">
            <label for="Nombre">{{ __('Empleado') }}</label><br>
            <select class="Empleado" name="Empleado" id="Empleado"></select>
          </div> 
          <div class="form-group">
            <label for="Tipo">{{ __('Tipo Movimientos') }}</label>
            <select class="form-select form-group" id="Tipo" name="Tipo">  
              <option selected value="">---------------------------------------------------------</option>          
                <option value="1">Salida</option>
                <option value="2">Ingreso</option>
              </select>
          </div> 
            
          <div class="form-group">
            <label for="Nombre">{{ __('Descripcion') }}</label>
                <textarea name="Descripcion" class="form-control" id="Descripcion" cols="30" rows="2" 
                placeholder="Marca, cantidades, describir que es lo que sale o ingresa "></textarea>
            </div> 
              
            <div class="form-group">
                <label for="Nombre">{{ __('Cantidad Transferir ') }}</label>
                <input type="text" class="form-control" name="Cantidad" autocomplete="off" autofocus required>
                </div>    
        @endforeach    
          <table class="table" >
            <tr ALIGN="center">
             <td >
               <div class="form-group" >
               <button type="submit" class="btn btn-success">{{ __('Crear') }}</button>       
               </div> 
            </td>
                 <td>
                   <div class="form-group ">
                    <a class="btn btn-danger" href="javascript: history.go(-1)">{{ __('Cancelar') }} </a> 
                   </div> 
                 </td>  
           </tr>
         </table>   
        </form>
      </div>
    </div>
  </div>

  <script>
    $( document ).ready(function() {
        $('#myModal').modal({  backdrop: 'static',
        keyboard: false}).modal('show')            
    });
    </script>
  

<script type="text/javascript">
  $('.Area').select2({
    theme: "material",
     language: {
              noResults: function() {return "Area no encontrada"; },
              searching: function(){ return "Buscando......." }
          },   
      
      ajax: {
          url: '/Ajax/Area',
          dataType: 'json',
          delay: 250,
          processResults: function (data) {
              return {
                  results: $.map(data, function (item) {
                      return {
                          text: item.Nombre,
                          id: item.Id
                      }
                  })
              };
          },
          cache: true
      }
  });
</script>

<script type="text/javascript">
    $('.Empleado').select2({
      theme: "material",
       language: {
                noResults: function() {return "Empleado no encontrada"; },
                searching: function(){ return "Buscando......." }
            },   
        
        ajax: {
            url: '/Ajax/Empleado',
            dataType: 'json',
            delay: 250,
            processResults: function (data) {
                return {
                    results: $.map(data, function (item) {
                        return {
                            text:item.cedula +" | " + item.nombre +" "+ item.apellidos,
                            id: item.id
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
  width: 100% !important; }
  .select2-container--material .select2-selection--single {
    background-color: transparent;
    border: none;
    border-bottom: 1px solid #ced4da;
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
    border-bottom: 1px solid #ced4da;
    border-radius: 0;
    outline: none; }      
  .select2-container--material .select2-results__option--highlighted[aria-selected] {
    background-color: #3498DB ;
    color: rgb(0, 0, 0); }
</style>