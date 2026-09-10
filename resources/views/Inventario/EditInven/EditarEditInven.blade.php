@extends("theme.$theme.layout")

@section('content')

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">

  <!-- Modal -->
  <div class="modal fade" id="myModal"  >
    <div class="modal-dialog " >
      <div class="modal-content"  >
        <div class="modal-header"  >
          <h5>EDITAR CANTIDAD DEL INVENTARIO</h5>         
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
        <form method="post" action="{{ route('actualizar_EditInven') }}">
            @csrf 
            <div class="card-body"  >            
                        <input type="hidden" class="form-control" name="Id" value={{$item->Id}} >
        <table class="table">
          <tr ALIGN="center">               
                    <div class="form-group"  ALIGN="center">
                        <label for="Division">{{$item->Division}} </label>           
                      </div>                   
            </tr>
            <tr ALIGN="center">
                <td>                  
                        <label for="Almacen">{{$item->Almacen}} </label>                           
                </td>               
            </tr>
            <tr ALIGN="center">
                <td>                    
                        <label for="Categoria">{{$item->Categoria}} </label>                              
                </td>               
            </tr>
            <tr ALIGN="center">
                <td>                   
                        <label for="Producto">{{$item->Producto}}</label>                             
                </td>               
            </tr>
            <tr ALIGN="center"> 
              <td>
                  <textarea class="form-group" name="Descripcion"  placeholder="Motivo de la modificación" autocomplete="off" rows="5" size=40 style="width:200px" >{{old('Descripcion')}}</textarea>                      
              </td>
            </tr>
            <tr ALIGN="center">
              <td>       
                  <label for="Producto">{{"Cantidad Actual: $item->Cantidades"}} </label><br>    
                  <input class="form-group" placeholder="Ingresa solo la diferencia " value="{{ old('Cantidad') }}" autocomplete="off" size=40 style="width:200px" name="Cantidad" >    
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
