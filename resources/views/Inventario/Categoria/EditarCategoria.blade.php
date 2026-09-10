@extends("theme.$theme.layout")

@section('content')

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">

  <!-- Modal -->
  <div class="modal fade" id="myModal"  >
    <div class="modal-dialog " >
      <div class="modal-content"  >
        <div class="modal-header"  >
          <h5 >EDITAR ALMACEN</h5>         
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
        <form method="post" action="{{ route('actualizar_Categoria',['id'=>$Categoria->id]) }}">
            @csrf 
      <div class="card-body">
        <div class="form-group">
          <input type="hidden" class="form-control" name="Id" value={{$Categoria->Id}} >
        </div>
        <div class="form-group">
          <label for="Nombre">{{ __('Nombre') }}</label>
          <input type="Nombre" class="form-control" name="Nombre" value="{{$Categoria->Nombre}} " autocomplete="off"  required>
        </div>

        <div class="form-group">
            <label for="Estado">{{ __('Estado') }}</label>
            <select class="form-select form-group" id="Estado" name="Estado">  
              <option selected value="">------------------------------</option>          
                <option value="1">Activo</option>
                <option value="2">Inactivo</option>
              </select>
          </div>


          <table class="table" >
            <tr ALIGN="center">
             <td >
               <div class="form-group" >
               <button type="submit" class="btn btn-success">{{ __('Actualizar') }}</button>       
               </div> 
            </td>
                 <td>
                   <div class="form-group ">
                    <a class="btn btn-danger" href="{{route('Categoria')}}">{{ __('Cancelar') }} </a> 
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
@endsection
