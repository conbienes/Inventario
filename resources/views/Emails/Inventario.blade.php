<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
 <html xmlns="http://www.w3.org/1999/xhtml">
  <head>
 <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
 <title>CONBIENES</title>
 <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
 </head>
 <body style="margin: 0; padding: 0;">
    <table align="center" border="1" cellpadding="0" cellspacing="0" width="600"> 
        <tr>  
            <br>


            @if (auth()->user()->id_div==1)
              <td align="center" bgcolor="#005484" width="300" height="200"> 
                <img src="{{asset("assets/$theme/dist/img/logo.jpg")}}"  alt="CONBIENES" width="250" height="150" />                
              </td>       
            @elseif (auth()->user()->id_div==2)
              <td align="center"  bgcolor="#66cccc" width="300" height="200"> 
                <img  src="{{asset("assets/$theme/dist/img/administracion.png")}}" alt="ADMINISTRACION MAYORCA" width="250" height="250" />                
              </td>               
            @elseif (auth()->user()->id_div==3)
              <td align="center" bgcolor="#000033" width="300" height="200"> 
                <img src="{{asset("assets/$theme/dist/img/bolera.png")}}"  alt="BOLERA MAYORCA" width="250" height="250" />                
              </td>                
            @else
              <td align="center" bgcolor="#005484" width="300" height="200"> 
                <img src="{{asset("assets/$theme/dist/img/logo.png")}}"  alt="CONBIENES" width="250" height="250" />                
              </td>             
            @endif                                 
        </tr> 
        <tr > 
            <td style="padding: 20px 15px 20px 15px;">               
                <strong><label > <font size=4 > Solicitante: </font></label></strong>
                @foreach ($Empleado as $item)
                <font size=4>{{$item->nombre}} {{$item->apellidos}}</font>
               @endforeach 
                    <br><br>
                <strong><label ><font size=4>Area Solicitante: </font></label></strong>
               @foreach ($Area as $item)
               <font size=4>{{$item->nombre}}</font>
              @endforeach  
              <br><br>
              @foreach ($FechaT as $item)
              <strong><label ><font size=4>Fecha Esperada: </font></label> </strong>       
              <font size=4>{{$item->FechaTentativa}}</font>              
              @endforeach  
              <br><br>
              <strong><label ><font size=4>Fecha de la Solicitud: </font></label> </strong>           
              <font size=4> <?php echo date("Y-m-d");?></font>
              <br><br>
              <font size=4>Conbienes empresa responsable del inventario.</font>
            </td>                                    
                                                                            
        </tr>   
        
         
         
        <tr>        
            <td bgcolor="#ffffff" style="padding: 10px ">                                                
                <table class="table table-striped table-bordered" >                                                                                                        
                    <tr>
                        <td>
                            <thead> 
                                <tr ALIGN="center">                                     
                                  <th width="200"scope="col">Producto</th>
                                  <th width="300"scope="col">Cantidad Solicitada</th>
                                </tr>
                              </thead>
                              @foreach ($pedidoinventario as $item)
                                <tr > 
                                  <td width="600"> <font size=2>{{$item->NombreP}}</font></td> 
                                    <td width="80" ALIGN="center"> <font size=2>{{$item->Cantidad}}</font></td>                                              
                                   </tr>                                                             
                                 @endforeach    
                        </td>                       
                    </tr>                                      
                </table>             
            </td>        
        </tr>
        <tr>        
          <td bgcolor="#ffffff" style="padding: 10px ">                                                
              <table class="table table-striped table-bordered" >                                                                                                        
                  <tr>
                    <strong><label for="text">{{ __('Observaciones: ') }}</label></strong>
                    @foreach ($Observacion as $item)                                             
                        <font size=2>{{$item->Observaciones}}</font>                                                                                                                            
                    @endforeach                   
                  </tr>                                      
              </table>             
          </td>        
      </tr>
       </table>
   </body>
 </html>
