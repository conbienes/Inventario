@extends("theme.$theme.layout")

@section('content')


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<div class="card card-primary"  >
  <div class="card-header " >
    <h3 class="card-title">{{ __('ALERTASTOCK') }}</h3>          
  </div>   
             
  <form href="{{route('AlertaStock',['id'=>Crypt::encrypt($Division->id)])}}" method="GET">
    <table CELLPADDING=5 >          
      <div class="form-group" > 
        <tr> 
          <td>
          </td>              
          <td>
            <div class="form-group ">          
              <input type="text"  class="form-control" name="texto" id="texto"  autocomplete="off" >
            </div> 
          </td>
          <td>
            <div class="form-group ">
              <button type="submit" name="Buscar" id="Buscar"  class="btn btn-primary">{{ __('Buscar') }}</button>
            </div> 
          </td>
        </div> 
        </form>
        <td>
          <form action="{{route('Exportar_Alertas')}}" method="POST">
            @csrf                                                                                                                                                                                       
            <div class="form-group ">
              <input class="date form-control"  type="hidden" id="IdEmpresa" name="IdEmpresa" value="{{$Division->id}}" readonly="readonly">                          
              <button type="submit" name="Buscar" id="Buscar"  class="btn btn-warning">{{ __('Exportar') }}</button>
            </div>                      
          </form>   
        </td>               
        </tr>  
    </table>    
          <div  class="card-header " >
            <div class="row justify-content-center">
   
                   <table class="table table-striped table-bordered" >                                         
                      @if (count($Inventario)<=0)      
                        <tr >
                          <td >NO HAY RESULTADO</td>
                        </tr>
                      @else
                      <thead>
                        <tr ALIGN="center">                                                                             
                          <th width="200"scope="col">Almacen</th>                         
                          <th width="150" scope="col">Categoría</th>                                             
                          <th width="150" scope="col">Producto</th>
                          <th width="100" scope="col">Cantidades</th> 
                        </tr>
                      </thead>

                      @foreach ($Inventario as $item)
                        <tr ALIGN="center">                          
                            <td width="80"> <font size=2>{{$item->Division}}</font></td>                             
                            <td width="100"><font size=2>{{$item->Categoria}}</font></td>                            
                            <td width="300"><font size=2>{{$item->Producto}}</font></td>                                                  
                            <td><font size=2> 
                              <a href="#" data-html="true" data-placement="left" data-toggle="tooltip" 
                              title="Cantidades en el inventario:<br>Minima: {{$item->CantidadMin}}<br>Actual: {{$item->Cantidades}} <br>Maxima: {{$item->CantidadMax}}" >
                               @if ($item->CantidadMin < $item->Cantidades )
                                <font color="#5DCF2F"><strong>{{$item->Cantidades}}</strong></font>
                               @else
                               <font color="red"><strong>{{$item->Cantidades}}</strong></font>
                               @endif
                              </a> 
                              </font>
                            </td>                           
                            
                          </tr>       
                         @endforeach                                                                                           
                      @endif                                                                                  
                  </table>  
                </div>
                  <div style="text-align:center;">
                    <table ALIGN="center">
                      <tr >
                        <td >
                          <p> {{$Inventario->total()}} Registros | Página {{$Inventario->currentPage()}} de {{$Inventario->lastPage()}}</p> 
                        </td>
                      </tr>
                      <tr>
                        <td>
                          {{$Inventario->links()}}
                         </td>
                         <tr>
                         <tr ALIGN="center">
                         <td>
                            <div class="form-group ">
                             <a class="btn btn-primary" href="{{route('InicioAlertaStock')}}">{{ __('Cancelar') }} </a> 
                            </div> 
                          </td>  
                      </tr>                                                           
                    </table>
                  </div>                                                  
              </div>                     
</div> 

<script>
  $(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();
  });

</script>


@endsection
