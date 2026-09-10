@extends("theme.$theme.layout")


@section('content')

<div class="card card-primary">
    <div class="card-header">
      <h3 class="card-title">{{ __('Anular Ticket') }}</h3>
    </div>
    
    
    <nav class="navbar navbar-light float-right">
    <form method="POST" action="{{route('buscar_ticket')}}" class="form-inline">
          @csrf
              <input name="id_estado" class="form-control mr-sm-2" autocomplete="off" type="search" placeholder="Buscar Ticket" aria-label="Search">
          
                 <button class="btn btn-success my-2 my-sm-0" type="submit" >{{ __('Buscar') }}</button>
            </form>
    </nav>

<div class="row">
        <div class="col-md-12">
            
                <table class="table table-hover">
                        @if ($buscar =='')
                        
                        @else   
                    <thead>
                          <tr>
                            <th scope="col">Ticket</th>
                            <th scope="col">Cédula</th>
                            <th scope="col">Nombre</th>
                           
                            <th scope="col">Restaurante</th>
                            <th scope="col">Fecha</th>
                            @if (auth()->user()->id_tipoempleado==1)
                            <th scope="col">Anular</th>
                            <th scope="col">Reimprimir</th>
                            @endif
                          </tr>
                    </thead>
                        <tbody>
                         @foreach ($buscar as $item )
                          <tr> 
                            <th scope="row">{{$item->id}}</th>
                            <td>{{$item->cedula}}</td>
                            <td>{{$item->Nombres}} {{$item->apellidos}}</td>
                           
                            <td>{{$item->Restaurante}}</td>
                            <td>{{$item->fecha}}</td>
                            @if (auth()->user()->id_tipoempleado==1)
                            <td> <a href="{{route('actualizar_ticket',['id'=>$item->id])}}"> <i class="fas fa-eraser nav-icon"></i>Anular</a></td>                    
                            <td> <a href="{{route('reimprimir_ticket',['id'=>$item->id])}}"> <i class="fas fa-eraser nav-icon"></i>Reimprimir</a></td>
                            @endif
                             
                          </tr>           
                          @endforeach
                          @endif
                        </tbody>
                      </table>
           </div>
          </div>
        </div>

@endsection