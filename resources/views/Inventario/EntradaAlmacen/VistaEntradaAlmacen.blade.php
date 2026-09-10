@extends("theme.$theme.layout")
@section('content')

<div class="card card-primary"  >
    <div class="card-header " >                                        
      <h3 class="card-title">{{ __('REGISTROS ALMACEN') }}</h3>                                                                                                              
    </div>                         
          <div class="card-body">                                            
          </div>
          <div class="card-primary" align="center" >
                  <table >
                      <tr>                       
                          <td>
                              <div class="info-box" >                                      
                                  <span class="info-box-icon bg-gradient-indigo"><i class="fas fa-paper-plane"></i></span>  
                                  @foreach ($Division as $item)
                                    <a href="{{route('Crear_EntAlmacen',['id'=>Crypt::encrypt($item->id)])}}" style="color: #000000" >
                                  @endforeach                                                                                                                         
                                              <div class="info-box-content" >
                                                <span class="info-box-text" ><label style="color: rgb(0, 0, 0)">
                                                  Ingreso pedido de compra</label></span>
                                              </div>
                                          </a>
                                </div>
                          </td> 
                             <td><br><br><br></td>            
                          <td >
                              <div class="info-box">
                                  <span class="info-box-icon bg-success"><i class="fab fa-creative-commons-pd-alt"></i></span>
                                  <a href="{{route('SolicPendFacturas',['id'=>Crypt::encrypt($item->id)])}}" style="color: #000000" >
                                  <div class="info-box-content">
                                    <span class="info-box-text" ><label style="color: rgb(0, 0, 0)">Procesar pedido de compra</label></span>                                                                      
                                    {{count($Activas)}}     
                                  </div>
                                </div>                                      
                              </a>
                          </td> 
                          <td><br><br><br></td>            
                          <td >
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="far fa-thumbs-up"></i></span>
                                <a href="{{route('SolicAprobadasFacturas',['id'=>Crypt::encrypt($item->id)])}}" style="color: #000000" >
                                <div class="info-box-content">
                                  <span class="info-box-text" ><label style="color: rgb(0, 0, 0)">Solicitudes Aprobadas</label></span>   
                                  {{count($Terminadas)}}                                  
                                </div>
                              </div>                                      
                            </a>
                        </td>                                                        
                      </tr>  
                      <tr>                                                                                 
                  </table>
                  <table>
                    <td align="center">
                        <div class="form-group">
                         <a class="btn btn-danger" href="{{route('EntAlmacen')}}">{{ __('Cancelar') }} </a> 
                        </div> 
                      </td>      
                  </tr>          
                </table>                      
              </div>                    
            </div>     
@endsection

