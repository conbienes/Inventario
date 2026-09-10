
@extends("theme.$theme.layout")
@section('content')

<div class="card card-primary"  >
    <div class="card-header " >
        <label size >{{ __('SOLICITUDES') }}</label>
    </div>
    <form action="{{route('SolicPendFacturas',['id'=>Crypt::encrypt($div)])}}" method="GET">
        <table CELLPADDING=5 >
          <div class="form-group" >
            <tr>
              <td>
              </td>
              <td>
                <div class="form-group ">
                  <input type="text"  class="form-control" name="texto" id="texto" autocomplete="off" >
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
                  <div class="form-group ">
                    <a class="btn btn-danger" href="{{route('Vista_EntAlmacen',['id'=>Crypt::encrypt($div)])}}" >{{ __('Cancelar') }} </a>
                  </div>
              </td>
            </tr>
        </table>

    <div  class="card-header " >
        <div class="row justify-content-center">

               <table class="table table-striped table-bordered" >
                  <thead>
                    <tr ALIGN="center">
					
                        <th width="200"scope="col">Proveedor</th>
                        <th width="200"scope="col">Nit</th>
                        <th width="200"scope="col">Precio Total</th>
                        <th width="200"scope="col">Orden de Pedido</th>
                        <th width="200"scope="col">Fecha Ingreso</th>
                        <th width="200"scope="col"># Remision</th>
                        <th width="200"scope="col">Estado</th>
                        <th width="200"scope="col">Ver</th>
                    </tr>
                  </thead>

                  @foreach ($SoliPendi as $item)
                  @if ($div==$item->IdDivisiones)
                      @if ($item->Estados===1)
                            <tr ALIGN="center">
						
                              <td width="80"> <font size=2>{{$item->Proveedor}}</font></td>
                              <td width="80"> <font size=2>{{$item->Nit}}</font></td>
                              <td width="80"> <font size=2>{{$item->PrecioTotal}}</font></td>
                              <td width="80"> <font size=2>{{$item->OrdenPedido}}</font></td>
                              <td width="80"> <font size=2>{{$item->FechaIngreso}}</font></td>
                              <td width="80"> <font size=2>{{$item->Nfactura}}</font></td>
                              <td width="80"> <font size=2>{{$item->Estado}}</font></td>
                              <td><font size=4> <a data-placement="left" data-toggle="tooltip" title="ver" href="{{route('AprobarFactura',['id'=>Crypt::encrypt($item->Id),'div'=> $div])}}"><i class="far fa-eye"></i></a> </font> </td>
                              </tr>
                          @else

                          @endif

                  @endif

                  @endforeach
              </table>
            </div>
              <div style="text-align:center;">
                <table ALIGN="center">
                  <tr >
                    <td >
                      <p> {{$SoliPendi->total()}} Registros | Página {{$SoliPendi->currentPage()}} de {{$SoliPendi->lastPage()}}</p>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      {{$SoliPendi->links()}}
                     </td>
                  </tr>
                </table>
              </div>
          </div>

@endsection






