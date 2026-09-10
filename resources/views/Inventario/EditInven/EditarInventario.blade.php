@extends("theme.$theme.layout")

@section('content')

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
<link rel="stylesheet" href="/path/to/select2.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@9.17.2/dist/sweetalert2.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/sweetalert2@9.17.2/dist/sweetalert2.min.css">

  <!-- Modal -->
  <div class="modal fade" id="myModal"  >
    <div class="modal-dialog " >
      <div class="modal-content"  >
        <div class="modal-header"  >
          <h5>Editar Ubicación Topografica</h5>         
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

@foreach($EditInven as $item)  
        <form method="post" action="{{ route('ActualizarInventario') }}">
            @csrf 
        <div class="card-body"  >            
        <input type="hidden" class="form-control" name="Id" value={{$item->Id}} >
        <table class="table">

          <tr ALIGN="center">                            
            <div class="form-group"  ALIGN="center">
              <label>Empresa</label> <br> 
                <label for="Division">{{$item->Division}} </label>           
              </div>                   
          </tr>
          <tr ALIGN="center">
              <td> 
                <div class="form-group"  ALIGN="center">
                <label>Categoría</label> <br>                   
                <label for="Categoria">{{$item->Categoria}} </label>  
              </div>                            
              </td>               
          </tr>
          <tr ALIGN="center">
              <td> 
                <label>Producto</label> <br>                   
                      <label for="Producto">{{$item->Producto}}</label>                             
              </td>               
          </tr> 
        </table> 
          <table class="table">        
            <tr ALIGN="center"> 
              <td>
                <div class="form-group"  ALIGN="center">
                  <label for="Estanteria">{{ __('Estanteria') }}</label>
                  <input type="text" class="form-control" name="Estanteria" autocomplete="off" autofocus required>
                  </div>
                </td>                           
                </tr>    
            <tr ALIGN="center"> 
              <td>
                <div class="form-group"  ALIGN="center">
                  <label for="Nombre">{{ __('Entrepaño') }}</label>
                  <input type="Entrepaño" class="form-control" name="Entrepaño" autocomplete="off" autofocus required>
                  </div>  
              </td>                                             
            </tr>    
            <tr ALIGN="center"> 
              <td>
                <div class="form-group"  ALIGN="center">
                  <label for="Gaveta">{{ __('Gaveta') }}</label>
                  <input type="text" class="form-control" name="Gaveta" autocomplete="off" autofocus required>
                  </div>  
              </td>              
            </tr>              
        </table>                                   
          <table class="table" >
            <tr ALIGN="center">
             <td >               
               <button type="submit" class="btn btn-success">{{ __('Actualizar') }}</button>                       
            </td>
                 <td>
                   <div class="form-group ">
                    <a class="btn btn-danger" href="javascript: history.go(-1)">{{ __('Cancelar') }} </a> 
                   </div> 
                 </td>  
           </tr>
         </table> 
        </div>  
        </form>
        @endforeach
      </div>
    </div>
  </div>

<script>
    $( document ).ready(function() {
        $('#myModal').modal({  backdrop: 'static',
        keyboard: false}).modal('show')            
    });
</script>

@endsection

<style>
  .select2-container--material {
   width: 100% !important; 
   font-size: 80% }
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