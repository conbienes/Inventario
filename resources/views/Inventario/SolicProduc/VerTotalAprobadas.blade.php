@extends("theme.$theme.layout")
@section('content')


<div class="card card-primary"  >
    <div class="card-header " >                                        
        <label size >{{ __('SOLICITUDES APROBADAS') }}</label>                                                                                                                  
    </div>          
    <form >
      <table>          
        <div class="form-group " >            
        </div> 
    </form>
</table>
               
    <div  class="card-header " >
        <div class="row justify-content-center">
               <table class="table table-striped table-bordered" > 
                  <thead>
                    <tr ALIGN="center">                                               
                      <th scope="col">Código</th>
                      <th width="200"scope="col">Fecha solicitada</th>
                      <th width="200"scope="col">Productos Solicitados</th>
                      <th width="200"scope="col">Estado</th>
                      <th width="200"scope="col">Ver</th>
                    </tr>
                  </thead>
                  @foreach ($pedidoinventario as $item)
                    <tr ALIGN="center"> 
                      <td width="80"> <font size=2>{{$item->Id}}</font></td> 
                        <td width="80"> <font size=2>{{$item->Dia}}</font></td>                                                                                                  
                        <td width="80"> <font size=2>{{$item->Cantidad}}</font></td>  
                        <td width="80"> @if ($item->Estado==1)
                            <font style="color: rgb(255, 0, 0)" size=2>PENDIENTE POR APROBAR</font></td> 
                        @else
                            <font style="color: rgb(36, 165, 19)"  size=2>CUMPLIDO</font></td> 
                        @endif
                        <td><font size=4> <a data-placement="left" data-toggle="tooltip" title="ver" href="{{route('VistaAprobados',['id'=>Crypt::encrypt($item->Id)])}}"> <i class="far fa-eye"></i></a> </font> </td>
                       </tr>       
                     @endforeach                                                                                 
              </table>  
            </div>
              <div style="text-align:center;">
                <table ALIGN="center">
                  <tr >
                    <td >
                      <p> {{$pedidoinventario->total()}} Registros | Página {{$pedidoinventario->currentPage()}} de {{$pedidoinventario->lastPage()}}</p> 
                    </td>
                  </tr>
                  <tr>
                    <td>
                      {{$pedidoinventario->links()}}
                     </td>                                       
                  </tr>  
                  <tr> 
                    <td>
                      <div class="row justify-content-center">
                        <a class="btn btn-danger" href="{{route('InicioInventario')}}">{{ __('Cancelar') }} </a> 
                        </div>   
                    </td>                                     
                  </tr>                                                         
                </table>
              </div>                                                  
          </div> 
</div>
           
@endsection