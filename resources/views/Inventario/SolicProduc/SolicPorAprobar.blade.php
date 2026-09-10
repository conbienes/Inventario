@extends("theme.$theme.layout")
@section('content')

<div class="card card-primary"  >
    <div class="card-header " >                                        
        <label size >{{ __('SOLICITUDES POR APROBAR') }}</label>                                                                                                                  
    </div>  

    @if (session("success"))

        
          <div class="alert alert-success alert-dismissible" data-auto-dismiss="3000">
        
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
              <ul>
                  <li>{{ session("success") }}</li>
              </ul>
          </div>

      @endif 

      @if (session("success1"))


      <div class="alert alert-warning alert-dismissible" data-auto-dismiss="3000">

          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
          <ul>
              <li>{{ session("success1") }}</li>
          </ul>
      </div>

      @endif     
       
    <form action="{{route('Solicitudes')}}" method="GET">
        <table CELLPADDING=5 >          
          <div class="form-group" > 
            <tr> 
              <td>
              </td>              
              <td>
                <div class="form-group ">          
                  <input type="text"  class="form-control" name="texto" id="texto" autocomplete="off" >
                </div> 
              </td>
              <td>
                <div class="form-group ">
                  <button type="submit" name="Buscar" id="Buscar"  class="btn btn-primary">{{ __('Buscar Solicitante') }}</button>
                </div> 
              </td>
            </div> 
            </form>
              <td>
                <div class="form-group ">
                  <a class="btn btn-success" name="Crear" id="Crear"  href="{{route('InicioInventario')}}">{{ __('Atrás') }}  </a>
                </div> 
              </td>
            </tr>  
        </table>
               
    <div  class="card-header " >
        <div class="row justify-content-center">

               <table class="table table-striped table-bordered" > 
                  <thead>
                    <tr ALIGN="center">                                               
                      <th scope="col">Código</th>
                      <th width="200"scope="col">Solicita</th>
                      <th width="100"scope="col"># Productos</th>
                      <th width="100"scope="col">Estado</th>                     
                      <th width="150"scope="col">Fecha Solicitud</th>
                      <th width="150"scope="col">Fecha Tentativa</th>
                      <th width="200"scope="col">Cumplir</th>
                    </tr>
                  </thead>

                  @foreach ($pedidoinventario as $item)
                  @if ($item->Estado==1)
                    <tr ALIGN="center"> 
                      <td width="80"> <font size=2>{{$item->Id}}</font></td> 
                        <td width="80"> <font size=2>{{$item->Nombre}} {{$item->Apellidos}}</font></td>                                                                                                  
                        <td width="80"> <font size=2>{{$item->Cantidad}}</font></td>  
                        <td width="80"> 
                            <font style="color: rgb(255, 0, 0)" size=2>PENDIENTE</font></td>                                               
                        <td width="80"> <font size=2>{{$item->Dia}}</font></td> 
                        <td width="80"> <font size=2>{{$item->fecha}}</font></td>   
                        <td><font size=4> <a data-placement="left" data-toggle="tooltip" title="ver" href="{{route('Cumplir',['id'=>Crypt::encrypt($item->Id)])}}"><i class="fas fa-flag-checkered"></i></a> </font> </td>
                       </tr> 
                       @else                            
                       @endif      
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
                </table>
              </div>                                                  
          </div> 
           
@endsection