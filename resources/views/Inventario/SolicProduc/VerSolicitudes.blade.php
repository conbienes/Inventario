@extends("theme.$theme.layout")

@section('content')

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>    
  <!-- Modal -->
  <div class="modal fade" id="myModal"  >
    <div class="modal-dialog modal-lg" >
      <div class="modal-content"  >
        <div class="modal-header"  >
         <h5 > <strong>PRODUCTOS SOLICITADOS</strong></h5>      
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered" > 
                <thead>
                  <tr ALIGN="center">
                    <th width="400"scope="col">Nombre del producto</th>
                    <th width="100"scope="col">Cantidad</th>
                    <th width="200"scope="col">Fecha Solicitud</th>
                  </tr>
                </thead>
            
                @foreach ($pedidoinventario as $item)
                  <tr ALIGN="center">
                      <td width="80"> <font size=2>{{$item->NombreP}}</font></td>                                                                                                  
                      <td width="80"> <font size=2>{{$item->Cantidad}}</font></td>                                                      
                      <td width="80"> <font size=2>{{$item->Fecha}}</font></td>  
                     </tr>       
                   @endforeach                                                                                 
            </table> 
            <table class="table" >
                <tr ALIGN="center">
                     <td>
                       <div class="form-group ">
                        <a class="btn btn-success" href="{{route('Pendientes')}}">{{ __('Cerrar') }} </a> 
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








