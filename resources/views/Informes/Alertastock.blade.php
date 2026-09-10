<table>
    <thead>
    <tr>
        <th>ALMACEN</th>
        <th>CATEGORIA</th>
        <th>PRODUCTO</th>
        <th>CANTIDADES ACTUALES</th>
        <th>CANTIDADES MINIMAS</th>
        <th>CANTIDADES MAXIMAS</th>

    </tr>
    </thead>
    <tbody>
        @foreach ($Inventario as $item)
        <tr>   
                   
           <td>{{$item->Almacen}} </td>  
           <td>{{$item->Categoria}}</td> 
           <td>{{$item->Producto}}</td>
           <td>{{$item->Cantidades}}</td>     
           <td>{{$item->CantidadMin}}</td>   
           <td>{{$item->CantidadMax}}</td>                                                
        </tr>
     @endforeach
    </tbody>
</table>

