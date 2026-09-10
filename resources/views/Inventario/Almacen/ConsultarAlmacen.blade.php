@extends("theme.$theme.layout")

@section('content')

<div class="card card-primary  " >
        <div class="card-header ">
          <h3 class="card-title">{{ __('Almacenes') }}</h3>          
        </div>

        <form action="{{route('Almacen')}}" method="GET">
          <table class="form-group col-lg-5 "  cellpadding="4" >          
            <div class="form-group"  >
              <tr>
                <td>                  
                </td>
                <td>
                  <div class="form-group ">       
                    <input type="text"  class="form-control" name="texto" id="texto" value="{{$Almacen}}" autocomplete="off" >
                  </div> 
                </td>
                <td>
                  <div class="form-group ">
                    <button type="submit" name="Buscar" id="Buscar"  class="btn btn-primary">{{ __('Buscar') }}</button>
                  </div> 
                </td>
              </form>
                <td>
                  <div class="form-group ">
                    <a class="btn btn-success" name="Crear" id="Crear"  href="{{route('Crear_Almacen')}}">{{ __('Crear') }}  </a>
                  </div> 
                </td>
              </tr>                             
            </div>  
          </table>
         
        
          <div  class="card-header " >
                <div class="row justify-content-center">
                   <table class="table" >                                         
                      @if (count($Almacen)<=0)      
                        <tr >
                          <td>NO HAY RESULTADO</td>
                        </tr>
                      @else
                      <thead>
                        <tr>                          
                          <th scope="col">Id</th>
                          <th scope="col">Almacen</th>
                          <th scope="col">Estado</th>
                          <th scope="col">Editar</th>
                        </tr>
                      </thead>

                      @foreach ($Almacen as $item)
                        <tr> 
                       
                            <td>{{$item->Id}} </td>  
                            <td>{{$item->Nombre}}</td> 
                            <td> 
                                @if ($item->Estado==1)
                                  Activo
                              @else
                                  Inactivo
                              @endif 
                            </td> 
                          <td> <a href="{{route('editar_Almacen',['id'=>$item->Id])}}"> <i class="fas fa-edit nav-icon"></i>Editar</a>  </td>  
                        </tr>           
                         @endforeach                                                                                           
                      @endif                                                                                   
                  </table> 
                  <div class="panel-body">
                    <p> {{$Almacen->total()}} Registros | Página {{$Almacen->currentPage()}} de {{$Almacen->lastPage()}}</p> 
                 
                </div>
                          {{$Almacen->links()}}
                        </div>
                </div><br>                          
</div>

@endsection
