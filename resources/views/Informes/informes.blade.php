@extends("theme.$theme.layout")

@section('content')

<div class="card card-primary">
  <div class="card-header">
    <h2 class="card-title">{{ __('Historico De Almuerzos') }}</h2>
   
  </div>
 <table>
        <tr> 
            <td>
              <form action="{{route('Exportar_excel')}}" method="POST">
                @csrf 
                    <div class="container">
                      <div class="row justify-content">
                        </div>
                         <div class="box-body">
                           <div class="row">
                           <div class="col-xs-3 offset-md-5 ">
                           <div class="form-group col-md-9 ">
                                <input class="date form-control"  type="hidden" id="FechaI" name="FechaI" value="{{$ini}}" readonly="readonly" >
                                </div>
                            </div>
                
                            <div class="col-xs-3">
                                <div class="form-group col-md-9">
                                        <input class="date form-control"  type="hidden" id="FechaF" name="FechaF" value="{{$fin}}" readonly="readonly">
                                </div>
                            </div>
                            
                            <div class="col-xs-1">
                                        <div class="form-group col-md-16">
                                            <button type="submit" class="btn btn-info pull-right" > <i class="fas fa-download nav-icon"> Descargar </i></button>
                                        </div>
                                    </div>
                                  
                                                                
                            </div>
                        </div>
                        <!-- /.box-body -->
                        </div>
                    </form>                   
            </td>
            <td>
                
                    <div class="col-xs-1">
                            <div class="form-group col-md-16">
                                <a class="btn btn-info pull-right" href="{{route('Informes')}}">{{ __('Nueva Busqueda ') }} <i class="fas fa-search nav-icon"></i></a> 
                            </div>
                         </div> 
            </td>   
            <td>
                <P ALIGN="center"> {{$info->total()}} registros</p> 
            </td>
        </tr> 
 </table>
 
    
  <div class="container">
        <div class="row justify-content-center">
            <table class="table table-hover"> 
                          <div class="panel-body">
                              
                              <div class="row justify-content-center">
                                
                         
                         </div>
                        </div>
                          <thead>
                                <tr>
                                  <th scope="col">ID</th>
                                  <th scope="col">Cedula</th>
                                  <th scope="col">Nombre</th>
                                  <th scope="col">División</th>
                                  <th scope="col">Restaurante</th>
                                  <th scope="col">Fecha Creación</th>
                                </tr>
                          </thead>
                              <tbody>
                                      @foreach ($info as $item)
                                      <tr>                
                                         <td>{{$item->id}} </td>  
                                         <td>{{$item->cedula}}</td>   
                                         <td>{{$item->Nombres}} {{$item->apellidos}}</td>
                                         <td>{{$item->div}}</td>
                                         <td>{{$item->Restaurante}}</td>
                                         <td>{{$item->created_at}}</td>
                                      </tr>
                                   @endforeach
                              </tbody>
                             
                    </table>                                                               
                       
                </div>
            </div>
       <br>
               
</div>
@endsection