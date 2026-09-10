<table>
    <thead>
    <tr>
        <th>Id</th>
        <th>DIVISION</th>
        <th>NOMBRE</th>
        <th>APELLIDOS</th>
        <th>PRODUCTO</th>
        <th>CANTIDAD SOLICITADA</th>
        <th>CANTIDAD APROBADA</th>
        <th>FECHA SOLICITUD</th>
        <th>ESTADO</th>

    </tr>
    </thead>
    <tbody>
        @foreach ($Solicitud as $item)
        <tr>   
                   
           <td>{{$item->IdSolicitud}} </td>  
           <td>{{$item->Division}}</td> 
           <td>{{$item->nombre}}</td>
           <td>{{$item->apellidos}}</td>     
           <td>{{$item->Nombre}}</td>   
           <td>{{$item->CantidadS}}</td>                                                
           <td>{{$item->CantidadAp}}</td>    
           <td>{{$item->created_at}}</td>    
           <td>{{$item->Estado}}</td>      
        </tr>
     @endforeach
    </tbody>
</table>

