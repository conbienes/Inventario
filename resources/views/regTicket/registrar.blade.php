@extends("theme.$theme.layout")


@section('content')



<style type="text/css">
      h4 {text-align: center; font-style: italic;  color: #000000  }
      h5 {text-align: right; color: #000000 }
      h6 {text-align: right; color: #000000 }
      a{text-align: center;}
</style>


<div class="row">
        <div class="col-md-12">
        
            <div class="card-body">
              <div class="inner">
                <table class="table"> 

             <h5><strong>{{date("Y-m-d H:i")}} </strong></h5> 
            
                <h4><strong>TICKET ALMUERZO</strong> </h4>
                <br>
                <h4>@switch ($empleado->id_div) 
                  @case (1)
                    CONBIENES S.A <br>
                    890.907.824
                    @break
                  @case (2):
                  Administracion Mayorca S.A.S<br>
                   901.344.877
                      @break
                  @case (3):
                   Mayor Diversion S.A.S<br>
                   901.120.630
                        @break		
                  @default:
                     No existe<br>
                     @break
                  </h4>
                  @endswitch
               
                        
                        <br>                        
                        <h4>Válido solo en <strong>{{$restaurante->nombre}}</strong></h4>
                       
                        <br> 
                                              
                        <h4>{{$empleado->nombre}} {{$empleado->apellidos}} </h4>   
                        
                        
                         <h4><strong>{{$empleado->cedula}}</strong></h4>  
                         
                <br>
                <h4>Firma:_____________________</h4>  
                <br>
                <h4>Redimible solo el día <strong>{{date("d-m-Y")}} </strong></h4>  
                <br>
                <h4>Recuerda que este ticket es único e intransferible</h4>  
                <br>
                
                    <tr>
                        <td>
                            <div class="  offset-md-5 ">
                              <a class="btn btn-primary" href="{{route('registrar_ticket',['restaurante'=>$restaurante->id ])}}">{{ __('Imprimir Ticket') }} </a>
                            </div> 
                          </td>  
                         <td>
                           <div class="offset-md-5 ">
                             <a class="btn btn-primary" href="{{route('ticket')}}">{{ __('Cancelar') }} </a>
                           </div> 
                         </td>  
                   </tr>
                 </table>                                
            </div>
           </div>
          </div>
        </div>
       
@endsection


