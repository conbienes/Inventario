@extends("theme.$theme.layout")

@section('content')



<div class="card card-primary">
        <div class="card-header">
          <h3 class="card-title">{{ __($restaurante->nombre) }}</h3>
        </div>
        
        @if($errors->any())
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h6><i class="icon fa fa-ban"></i>
                    @foreach ($errors->all() as $error)
                        <i>{{ $error }}</i><br>
      
                    @endforeach
                </h6>
            
        </div>
        @endif


          
        <form method="post" action="{{ route('actualizar_restaurante',['id'=>$restaurante->id]) }}">
                @csrf 
          <div class="card-body">
            <div class="form-group">
              <label for="nit">{{ __('Nit') }}</label>
              <input type="cedula" class="form-control" name="nit" value={{$restaurante->nit}} autocomplete="off" autofocus >
            </div>
            <div class="form-group">
              <label for="nombre">{{ __('Nombre') }}</label>
              <input type="nombre" class="form-control"  name="nombre" value={{$restaurante->nombre}}  autocomplete="off" autofocus>
            </div>
            <table class="table">
             
                <td>
                   
                    <div class="row">
                        <div class="col-sm-5">
                          <!-- select -->
                          <div class="form-group">
                            <label>{{ __('Estado') }}</label>
                            <select class="custom-select" name="id_estado" id="id_estado" >
                                    @foreach($Estado as $item)
                                    <option value="{{$item->id}}"> {{$item->nombre}} </option>
                                    @endforeach
                            </select>
                          </div>
                        </div> 
                    </div>
                </td>                     
        </table>  
            

                    
                         <br>
                          <table class="table">
                             <tr>
                              <td>
                                <div class="  offset-md-5 ">
                                <button type="submit" class="btn btn-primary">{{ __('Actualizar') }}</button>
                                </div> </td>
            </form>
                                  <td>
                                    <div class="  offset-md-5 ">
                                      <a class="btn btn-primary" href="{{route('consultar_restaurante')}}">{{ __('Cancelar') }} </a>
                                    </div> 
                                  </td>  
                            </tr>
                          </table>   
              
            
</div>

@endsection