@extends("theme.$theme.layout")
@section('content')

<div class="card card-primary"  >
    <div class="card-header " >                                        
        <label size >{{ __('SOLICITUDES APROBADAS') }}</label>                                                                                                                  
    </div>          
    <form >
      <table  ALIGN="center">          
        <div class="form-group" >          
          </td>
                      
           </div> 
          </form>
</table>
               
    <div  class="card-header " >
        <div class="row justify-content-center">
               <table class="table table-striped table-bordered" > 
                  <thead>
                    <tr ALIGN="center">                                               
                      <th scope="col">Código</th>
                      <th width="100"scope="col">Fecha Solicitud</th>
                      <th width="200"scope="col">Empleado</th>
                      <th width="100"scope="col"># Productos</th>
                      <th width="80"scope="col">Estado</th>
                      <th width="70"scope="col">Ver</th>
                    </tr>
                  </thead>
                  @foreach ($pedidoinventario as $item)
                    
                        <tr ALIGN="center"> 
                          <td width="80"> <font size=2>{{$item->Id}}</font></td> 
                            <td width="80"> <font size=2>{{$item->Dia}}</font></td>                                                                                                  
                            <td width="80"> <font size=2>{{$item->Empleado}} {{$item->Apellido}}</font></td>    
                            <td width="80"> <font size=2>{{$item->Cantidad}}</font></td>  
                      @if ($item->Estado==2)
                            <td width="80"> 
                                <font style="color: rgb(42, 170, 2)" size=2>APROBADAS</font></td>                       
                            <td><font size=4> <a data-placement="left" data-toggle="tooltip" title="ver" href="{{route('VistaAprobados',['id'=>Crypt::encrypt($item->Id)])}}"> <i class="far fa-eye"></i></a> </font> </td>                                                                                       
                      @else
                            <td width="80"> 
                              <font style="color: rgb(170, 2, 2)" size=2>PENDIENTES</font></td>                       
                            <td><font size=4> <a data-placement="left" data-toggle="tooltip" title="ver" href="{{route('VistaAprobados',['id'=>Crypt::encrypt($item->Id)])}}"> <i class="far fa-eye"></i></a> </font> </td>                                                                                       
                      @endif

                     @endforeach  
                    </tr>                                                                                
              </table>  
            </div>
             
              <div style="text-align:center;">
                <table ALIGN="center">
                  <tr ALIGN="center">
                    <td >
                      <p> {{$pedidoinventario->total()}} Registros | Página {{$pedidoinventario->currentPage()}} de {{$pedidoinventario->lastPage()}}</p> 
                    </td>
                  </tr>
                  <tr ALIGN="center">
                    <td>
                      {{$pedidoinventario->appends(Request::except('page'))->links()}}
                     </td>
                  </tr>
                     <tr ALIGN="center">
                     <td>
                        <div class="form-group ">
                         <a class="btn btn-primary" href="javascript: history.go(-1)">{{ __('Cancelar') }} </a> 
                        </div> 
                      </td>  
                  </tr>                                                           
                </table>
              </div>  
          </div> 
</div>  

@endsection
