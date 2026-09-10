@extends("theme.$theme.layout")

@section('content')

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
          <h5 >NOTIFICACIONES</h5>         
        </div>
        
        @if ($Enviado==1)   
          <div class="alert alert-success alert-dismissible" data-auto-dismiss="3000">
       
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <ul>
                <li>El Mensaje Fue enviado</li>
            </ul>
         </div>    
    @endif

          <form method="POST" action="{{ route('NotificarPersonal') }}">
            @csrf          
            <table ALIGN="center"  >
              <tr >                     
                <div class="card-body">
                  <div class="form-group">
                    <label for="Nombre">{{ __('Ingrese Correo Electronico') }}</label><BR>
                  <select class="Correo" name="Correo" id="Correo"> </select>
                </div>                                         
              </div> 
              </tr>
              <tr>
                  <div class="form-group" ALIGN="center">
                    <button type="submit" class="btn btn-success">{{ __('Enviar Notificaciones') }}</button>       
                  </div> 
              </tr>
            </table>  
            <input name="registroF" type="hidden" id="registroF" value="{{$registroF}}">         
        </form> 

        <table >
          <th>
            <td>
              <div class="form-group " ALIGN="center">
                <a class="btn btn-danger" href="{{route('EntAlmacen')}}">{{ __('Terminar') }} </a> 
            </div> 
            </td>             
          </th>
        </table>                    
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
  $('.Correo').select2({
    theme: "material",
     language: {
              noResults: function() {return "Correo no encontrada"; },
              searching: function(){ return "Buscando......." }
          },   
      
      ajax: {
          url: '/Ajax/Correos',
          dataType: 'json',
          delay: 250,
          processResults: function (data) {
              return {
                  results: $.map(data, function (item) {
                      return {
                          text: item.Correo,
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