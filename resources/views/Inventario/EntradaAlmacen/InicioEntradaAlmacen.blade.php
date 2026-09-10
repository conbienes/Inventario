@extends("theme.$theme.layout")
@section('content')


<div class="card card-primary"  >
    <div class="card-header " >                                        
      <h3 class="card-title">{{ __('ENTRADAS ALMACEN') }}</h3>                                                                                                              
    </div>

    @if (session("succes"))

   
            <div class="alert alert-success alert-dismissible" data-auto-dismiss="3000">
           
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <ul>
                    <li>{{ session("succes") }}</li>
                </ul>
            </div>
        
        @endif 

        @if (session("succes1"))

   
        <div class="alert alert-warning alert-dismissible" data-auto-dismiss="3000">
       
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <ul>
                <li>{{ session("succes1") }}</li>
            </ul>
        </div>
    
    @endif  
          <div class="card-body" align="center">
                  <table > 
                      <tr>                                                                                     
                          <td >
                            @foreach ($Division as $item)                
                              <div class="info-box" >                                      
                                <span class="info-box-icon "> <img src="{{asset("assets/$theme/dist/img/8.png")}}"/> </span>                                                                               
                                          <a href="{{route('Vista_EntAlmacen',['id'=>Crypt::encrypt($item->id)])}}" style="color: #000000" >
                                              <div class="info-box-content" >
                                                  <span class="info-box-text">
                                                  {{$item->nombre}} </i>
                                                  </span>
                                              </div>
                                          </a>
                                </div>
                                @endforeach  
                          </td>                           
                      </tr> 
                      <td align="center">
                        <div class="form-group">
                         <a class="btn btn-primary" href="{{route('InicioInventario')}}">{{ __('Cancelar') }} </a> 
                        </div> 
                      </td>
                  
                  </table>                      
              </div> 
            </div>       

@endsection