            <table>
                    <thead>
                    <tr>
                        <th>Id</th>
                        <th>CEDULA</th>
                        <th>NOMBRE</th>
                        <th>APELLIDOS</th>
                        <th>DIVISION</th>
                        <th>RESTAURANTE</th>
                        <th>FECHA</th>
                        
                    </tr>
                    </thead>
                    <tbody>
                            @foreach ($info as $item)
                            <tr>
    
                               <td>{{$item->id}} </td>  
                               <td>{{$item->cedula}}</td>   
                               <td>{{$item->Nombres}} </td>
                               <td>{{$item->apellidos}}</td>
                               <td>{{$item->div}}</td>
                               <td>{{$item->Restaurante}}</td>
                               <td>{{$item->created_at}}</td>
                            </tr>
                            
                            
                             @endforeach  
                    </tbody>
                </table>


                