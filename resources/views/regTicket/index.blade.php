@extends("theme.$theme.layout")

@section('content')


<div class="card card-primary">
        <div class="card-header">
          <h3 class="card-title">{{ __('Imprimir Ticket') }}</h3>
        </div>
      
      <style type="text/css">
          h4 {text-align: center; font-style: italic;  color: #000000  }
          h5 {text-align: right; color: #000000 }
          h6 {text-align: right; color: #000000 }
          a{text-align: center;}
    </style>
    
    
    <div class="row">
            <div class="col-md-12">
             
                <div class="card-body">
                    <strong><h5>{{date("Y-m-d H:i")}} </h5> </strong>
                    <h4>Bienvenidos al beneficio del restaurante   @if (auth()->user()->id_div==1)
                      Mayorca Inversiones       
                      @elseif (auth()->user()->id_div==2)
                      Administracion Mayorca
                      @elseif (auth()->user()->id_div==3)
                      Bolera Mayorca
                      @else
                      NA
                      @endif</h4>
                      <h4>te recordamos que el mismo es personal e intransferible</h4>
                      <h4>Cual opción quieres el día de hoy:</h4>    

                </div>
               </div>
              </div>

   
    <table>
      <tr>
        
            @foreach ($restaurante as $restaurante)
            @if ($restaurante->id_estado==1)
            <td>
                <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{$restaurante->nombre}}</h3> 
                      
                </div>
                
                <div class="icon">
                  <i class="ion ion-ios-nutrition"></i>
                </div>
              
                <a href="{{route('crear_ticket',['persona'=>auth()->id(),'restaurante'=>$restaurante->id ])}}" class="small-box-footer">Imprimir <i class="fas fa-arrow-circle-right"></i></a>
              </div>
              </td>
          @endif
        @endforeach
          </tr>
      
    </table>
  
            </div>
  
@endsection