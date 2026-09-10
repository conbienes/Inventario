@extends("theme.$theme.layout")

@section('content')

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>    
  <!-- Modal -->
  <div id="myModal" class="modal fade">
    <div class="modal-header ">
        <div class="modal-content">
              <div class="modal-header" >
         <h5 > <strong>APROBAR SOLICITUD</strong></h5>      
        </div>
        <div class="card-body">
            <table class="table-striped table-bordered" > 
              <form action="{{route('AprobarSolicitud')}}" method="POST">
                @csrf 
                <thead>
                  <tr ALIGN="center">
                    <th width="450"scope="col">Producto</th>
                    <th width="100"scope="col">Cantidad Solicitadas</th>  
                    <th width="200"scope="col">Cantidad X Aprobar</th>                   
                    <th width="100"scope="col">Cantidad Inventario</th> 
                    <th width="650"scope="col">Comentario</th>  
                  </tr>
                </thead>
            
                @foreach ($pedidoinventario as $item)
                    <tr ALIGN="center">
                      <td width="80"> <font size=2><input type="hidden" value="{{$item->IdPedido}}" name="IdPedido[]"> {{$item->NombreProduc}}"</font></td>                                                                                                  
                      <td width="80"> <font size=3>{{$item->Cantidad}}</font></td>                                                                          
                      <td ><input type="number" name="Catidad[]" id="Catidad[]" style="width : 50px; heigth : 10px" autocomplete="off" required></td> 
                      <td width="80"> <font size=3>{{$item->Cantidades}}</font></td>   
                      <td > <textarea name="Comentario[]" maxlength="200" id="Comentario" cols="80" rows="1" autocomplete="off"></textarea></td>                          
                    </tr> 
                    <input type="hidden" value="{{$item->Id}}" name="IdSolicitud"></font></td>       
                    <input type="hidden" value="{{$item->IdInventario}}" name="IdInventario[]"></font></td>  
                    <input type="hidden" value="{{$item->IdArea}}" name="IdArea"></font></td>       
                    <input type="hidden" value="{{$item->IdEmpleado}}" name="IdEmpleado"></font></td>        
                   @endforeach                                                                                                      
            </table> 
            <table class="table" style="width : 50px; heigth : 10px" ALIGN="center" >
              <tr >
                <td >
                  <div class="form-group" >
                  <button type="submit" class="btn btn-success">{{ __('Aprobar') }}</button>       
                  </div> 
               </td>
              </form>  
                    <td>
                      <div class="form-group ">
                       <a class="btn btn-danger" href="javascript: history.go(-1)">{{ __('Cancelar') }} </a> 
                      </div> 
                    </td>  
              </tr>
             </table> 
        </div>  
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








