@extends("theme.$theme.layout")

@section('content')

<div class="card card-primary"  >
    <div class="card-header " >                                        
        <h2 class="card-title">{{ __('ARQUEO') }}</h2>                                                                                                                  
    </div> 
   
        <table align="center">
            <tr > 
                <div class="form-group " >                             
                <td >
                    <form action="{{route('Exportar_Arqueo')}}" method="POST">
                    @csrf                                                                      
                            @foreach ($Inventario as $item)                                                                                                             
                                <input class="date form-control"  type="hidden" id="IdEmpresa" name="IdEmpresa" value="{{$item->IdEmpresa}}" readonly="readonly">                          
                            @endforeach                               
                                <button type="submit" class="btn btn-warning" >
                                    <i class="fas fa-download nav-icon"> Exportar</i>
                                </button>                            
                        </form>                      
                </td>             
                <td >
                    <form action="{{route('import')}}" method="post" enctype="multipart/form-data">
                        {{csrf_field()}}
                    
                        <table > 
                            
                                <td >                                      
                                    <button type="submit" class="btn btn-success">  <i class="fa fa-upload" aria-hidden="true"> Importar</i></button>                               
                                </td>
                                <td>                                  
                                    <input type="file"  id="Arqueos" name="Arqueos" required>                                                      
                                </td>                              
                                                                          
                        </table>                                                                        
                    </form>                 
                </td> 
                </div>                                  
            </tr> 
        </table>  
    <div class=""  >
        <div  class="card-header " >
            <div class="row justify-content-center">
                <table class="table table-striped table-bordered" > 
                    <thead>
                        <tr ALIGN="center">                                                                            
                            <th width="170"scope="col">Almacen</th>                          
                            <th width="150" scope="col">Categoría</th>                                       
                            <th width="150" scope="col">Producto</th>
                            <th width="50" scope="col">Cantidades</th> 
                          </tr>
                    </thead>  
                        @foreach ($Inventario as $item)
                        <tr ALIGN="center"> 
                         
                            <td width="130"> <font size=2>{{$item->Division}}</font></td> 
                            <td width="100"><font size=2>{{$item->Categoria}}</font></td>                            
                            <td width="400"><font size=2>{{$item->Producto}}</font></td>  
                            <td width="50"><font size=2>{{$item->Cantidades}}</font></td>                                                      
                                                 
                            </tr>   
                    @endforeach                                                                              
                </table>  
            </div>
               <div style="text-align:center;">
                    <table ALIGN="center">
                      <tr >
                        <td ALIGN="center">
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
                             <a class="btn btn-primary" href="{{route('In_Arqueo')}}">{{ __('Cancelar') }} </a> 
                            </div> 
                          </td>  
                      </tr>                                                           
                    </table>
                  </div>          
        </div> 
    
    </div> 
</div>
@endsection


<style>

.custom-input-file .input-file {
 border: 10000px solid transparent;
 cursor: pointer;
 font-size: 10000px;
 margin: 0;
 opacity: 0;
 outline: 0 none;
 padding: 0;
 position: absolute;
 right: -1000px;
 top: -1000px;
}
</style>