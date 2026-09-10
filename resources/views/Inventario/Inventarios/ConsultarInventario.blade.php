@extends("theme.$theme.layout")

@section('content')


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>


<div class="card card-primary"  >
        <div class="card-header " >
          <h3 class="card-title">{{ __('INVENTARIO') }}</h3>
        </div>

        @if (session("success"))


            <div class="alert alert-success alert-dismissible" data-auto-dismiss="3000">

                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <ul>
                    <li>{{ session("success") }}</li>
                </ul>
            </div>

        @endif

        @if (session("success1"))


        <div class="alert alert-warning alert-dismissible" data-auto-dismiss="3000">

            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <ul>
                <li>{{ session("success1") }}</li>
            </ul>
        </div>

    @endif
        <form action="{{route('Consultar_Inventario',['id'=>Crypt::encrypt($Division->id)])}}" method="GET">
          <table CELLPADDING=5 >
            <div class="form-group " >
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
                  <div class="form-group ">
                    <a class="btn btn-success" name="Crear" id="Crear"  href="{{route('Crear_Inventario')}}">{{ __('Crear') }}  </a>
                  </div>
                </td>
              </tr>
          </table>

          <div  class="card-header " >
            <div class="row justify-content-center">

                   <table class="table table-sm table-bordered" >
                      @if (count($Inventarios)<=0)
                        <tr >
                          <td >NO HAY RESULTADO</td>
                        </tr>
                      @else
                      <thead>
                        <tr ALIGN="center">
                          <th scope="col"><font size=2> Id </th>
                          <th scope="col"><font size=2> Almacen</th>
                          <th scope="col"><font size=2> Categoría</th>
                          <th scope="col"><font size=2>  Estanteria - Entrepaño - Gaveta</th>
                          <th scope="col"><font size=2> Producto</th>
                          <th scope="col"><font size=2> Cantidades</th>
                          <th scope="col"><font size=2> </th>
                        </tr>
                      </thead>
                      @foreach ($Inventarios as $item)
                        <tr ALIGN="center">
                            <td ><font size=2>{{$item->Id}}</font></td>
                            <td ><font size=2>{{$item->Division}}</font></td>
                            <td ><font size=2>{{$item->Categoria}}</font></td>
                            <td ><font size=2>{{$item->Estanteria}} - {{$item->Entrepano}} - {{$item->Gaveta}} </font></td>
                            <td ><font size=2>{{$item->Producto}}</font></td>
                            <td ><font size=2>
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
                            <td>
                            <font size=3> <a data-placement="left" data-toggle="tooltip" title="Editar Ubicación Topográfica" href="{{route('EditarInventario',['Id'=>Crypt::encrypt($item->Id)])}}"> <i style="color: rgb(0, 0, 0)" class="fas fas fa-marker"></i></a> </font>
                            <font size=3> <a data-placement="left" data-toggle="tooltip" title="Editar Cantidades" href="{{route('editar_EditInven',['Id'=>Crypt::encrypt($item->Id)])}}"> <i style="color: green" class="fas fa-edit nav-icon"></i></a> </font>
                            <font size=3> <a data-placement="left" data-toggle="tooltip" title="Movimientos" href="{{route('editar_Movimientos',['id'=>Crypt::encrypt($item->Id)])}}"> <i style="color: blue" class="fas fa-shipping-fast"></i></a></font>
                            <font size=3> <a data-placement="left" data-toggle="tooltip" title="Ver Movimientos Producto" href="{{route('Consultar_Producto',['id'=>Crypt::encrypt($item->Id)])}}"> <i style="color: rgba(255, 136, 0, 0.685)" class="fas fa-exclamation-circle"></i></a></font>
                          </tr>
                         @endforeach
                      @endif
                  </table>
                </div>
                  <div style="text-align:center;">
                    <table ALIGN="center">
                      <tr >
                        <td >
                          <p> {{$Inventarios->total()}} Registros | Página {{$Inventarios->currentPage()}} de {{$Inventarios->lastPage()}}</p>
                        </td>
                      </tr>
                      <tr>
                        <td>
                          {{$Inventarios->links()}}
                         </td>
                         <tr>
                         <tr ALIGN="center">
                         <td>
                            <div class="form-group ">
                             <a class="btn btn-primary" href="{{route('Inventario')}}">{{ __('Cancelar') }} </a>
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











