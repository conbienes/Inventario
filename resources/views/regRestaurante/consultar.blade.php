@extends("theme.$theme.layout")

@section('content')

<div class="card card-primary">
        <div class="card-header">
          <h3 class="card-title">{{ __('Restaurantes') }}</h3>
        </div>
        <div class="row">
            <div class="col-md-12">
                    <table class="table table-hover"> 
                        <div class="panel-body">
                            <div class="row justify-content-center">
                        <p> {{$Restaurante->total()}} registros | Pagina {{$Restaurante->currentPage()}} de {{$Restaurante->lastPage()}}</p> 
                       </div>
                      </div>
                        <thead>
                              <tr>
                                <th scope="col">Nit</th>
                                <th scope="col">Restaurante</th>
                                <th scope="col">Estado</th>
                                <th scope="col">Editar</th>
                              </tr>
                        </thead>
                            <tbody>
                                @foreach ($Restaurante as $item)
                                <tr>
                                 <td>{{$item->nit}}</td>  
                                 <td>{{$item->Nombre}} </td> 
                                 <td>{{$item->Estado}}</td>    
                                 <td><a href="{{route('editar_restaurante',['id'=>$item->id])}}"> <i class="fas fa-edit nav-icon"></i>Editar</a></td>  
                               </tr>
                                 @endforeach
                                 
                            </tbody>
                          </table>
               </div>
              </div>
        <div class="container">
                <div class="row justify-content-center">
                   <table class="table">

                                        
                                </table>    
                        </div>
                    </div>
             <br>
                          
</div>

@endsection