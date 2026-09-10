@extends("theme.$theme.layout")

@section('content')

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>    
  <!-- Modal -->
  <div class="modal fade" id="myModal"  >
    <div class="modal-dialog modal-lg" >
      <div class="modal-content"  >
        <div class="modal-header"  >
         <h5 > <strong>Movimientos Producto</strong></h5>      
        </div>
        <div class="card-body">
            <table class="table-striped table-bordered" >                       
                <thead>
                  <tr ALIGN="center">
                    <th width="300"scope="col">Producto</th>
                    <th width="100"scope="col">Cantidad</th>
                    <th width="200"scope="col">Tipo de Movimiento</th>
                    <th width="300"scope="col">Descripcion</th>
                    <th width="130"scope="col">Fecha</th>
                    <th width="300"scope="col">Empleado</th>
                    
                    
                  </tr>
                </thead>            
                @foreach ($Inventario as $item)
                    <tr ALIGN="center">
                      <td width="80"> <font size=2>{{$item->Producto}}</font></td>    
                        <td width="80"> <font size=2>{{$item->Cantidad}}</font></td> 
                        <td width="80"> <font size=2>{{$item->Nombre}}</font></td>                          
                        <td width="80"> <font size=2>{{$item->Descripcion}}</font></td>  
                        <td width="80"> <font size=2>{{date("d-m-Y" , strtotime($item->FCreacion))}}</font></td>                                                                   
                        <td width="80"> <font size=2>{{$item->Empleado}} {{$item->Apellidos}} </font></td>
                    </tr>                   
                   @endforeach                                                                                                      
            </table> 

            <table class="table" style="width : 50px; heigth : 10px" ALIGN="center" >                                     
                    <td>
                      <div class="form-group">
                        <a class="btn btn-danger" href="javascript: history.go(-1)">{{ __('Cancelar') }} </a> 
                      </div> 
                    </td>              
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








