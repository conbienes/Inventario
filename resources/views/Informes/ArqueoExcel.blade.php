<table>
    <thead>
    <tr>
        <th>Id</th>
        <th>ALMACEN</th>
        <th>CATEGORIA</th>
		<th>CODIGO</th>
        <th>PRODUCTO</th>
        <th>ESTANTERIA - ENTREPAÑO - GAVETA</th>
        <th>CANTIDADES</th>
        <th>CANT_ACTUAL</th>
        <th>COMENTARIO</th>
		<th>CANTIDADES MIN</th>
		<th>CANTIDADES MAX</th>
        <th>IdEmpresa</th>
        <th>EMPRESA</th>

    </tr>
    </thead>
    <tbody>
        @foreach ($Inventario as $item)
        <tr>   
          <td>{{$item->Id}}</td>             
           <td>{{$item->Almacen}} </td>  
           <td>{{$item->Categoria}}</td>  
		   <td>{{$item->Codigo}}</td>  
           <td>{{$item->Producto}}</td>
           <td>{{$item->Estanteria}} - {{$item->Entrepano}} - {{$item->Gaveta}} </td>
           <td>{{$item->Cantidades}}</td>
           <td></td>
           <td></td>
           <td>{{$item->CantidadMin}}</td>
           <td>{{$item->CantidadMax}}</td>
           <td>{{$item->IdEmpresa}}</td>
           <td>{{$item->Division}}</td>
        </tr>
     @endforeach
    </tbody>
</table>

