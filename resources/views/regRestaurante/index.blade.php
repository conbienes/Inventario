@extends("theme.$theme.layout")

@section('content')

<div class="card card-primary">
    <div class="card-header">
      <h3 class="card-title">{{ __('Registrar Restaurante') }}</h3>
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

    @if (session("mensaje"))
    <div class="alert alert-success alert-dismissible" data-auto-dismiss="3000">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        
        <ul>
            <li>{{ session("mensaje") }}</li>
        </ul>
    </div>
@endif
      
    <form method="POST" action="{{ route('crear_restaurante') }}" enctype="multipart/form-data" >
      @csrf
      <div class="card-body">
        <div class="form-group">
          <label for="nit">{{ __('Nit') }}</label>
          <input type="text" class="form-control" name="nit" value="{{ old('nit') }}"  autocomplete="off" autofocus >
        </div>
        <div class="form-group">
          <label for="nombre">{{ __('Nombre') }}</label>
          <input type="text" class="form-control"  name="nombre" value="{{ old('nombre') }}"  autocomplete="off" autofocus>
        </div>

      </table> 
            
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
        <div class="  offset-md-5 ">
          <button type="submit" class="btn btn-primary">{{ __('Crear') }}</button>
        </div>
      </div> 
        </form>
</div>




@endsection