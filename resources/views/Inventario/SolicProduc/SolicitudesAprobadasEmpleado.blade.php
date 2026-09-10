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
                    <th width="200"scope="col">Comentario</th>
                  </tr>
                </thead>            
                @foreach ($pedidoinventario as $item)
                    <tr ALIGN="center">
                        <td width="80"> <font size=2>{{$item->NombreProduc}}</font></td> 
                        <td width="80"> <font size=2>{{$item->Fecha}}</font></td>                                                                                                                        
                          @if ($item->CantAprobadas==$item->Cantidad)
                            <td width="80"> <font size=3 style="color: green"> {{$item->CantAprobadas}} </font></td>  
                          @else
                            <td width="80"> <font size=3 style="color: red"> {{$item->CantAprobadas}} </font></td>  
                          @endif
                        <td width="80"> <font size=3>{{$item->Cantidad}}</font></td>  
                        <td width="80"> <font size=2>{{$item->Comentario}}</font></td>                      
                    </tr>                   
                  @endforeach                                                                                                      
            </table> 
            <table class="table" style="width : 50px; heigth : 10px" ALIGN="center" >                                     
                    <td>
                      <div class="form-group">
                       <a class="btn btn-danger" href="javascript:history.back()">{{ __('Cancelar') }} </a> 
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








