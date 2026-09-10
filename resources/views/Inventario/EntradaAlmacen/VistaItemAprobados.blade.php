@extends("theme.$theme.layout")

@section('content')

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>    
  <!-- Modal -->
  <div class="modal fade" id="myModal"  >
    <div class="modal-dialog modal-lg" >
      <div class="modal-content"  >
        <div class="modal-header"  >
         <h5 > <strong>Productos Aprobados</strong></h5>      
        </div>
        <div class="card-body">
            <table class="table-striped table-bordered" >                       
                <thead>
                  <tr ALIGN="center">
                    <th width="200"scope="col">Producto</th>
                    <th width="200"scope="col">Fecha Solicitud</th>
                    <th width="200"scope="col">Cantidad Aprobadas</th>
                    <th width="200"scope="col">Cantidad Solicitadas</th>
                  </tr>
                </thead>            
                @foreach ($itemfactura as $item)
                    <tr ALIGN="center">
                        <td width="80"> <font size=2>{{$item->Producto}}</font></td> 
                        <td width="80"> <font size=2>{{$item->Comentario}}</font></td>                          
                        <td width="80"> <font size=2>{{$item->CantidadA}}</font></td>  
                        <td width="80"> <font size=2>{{$item->CantidadS}}</font></td>                      
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








