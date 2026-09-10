@extends("theme.$theme.layout")

@section('content')

  <div class="container">
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/css/bootstrap-datepicker.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/js/bootstrap-datepicker.js"></script>
    <div class="container">
  </div>
</div>

    


<div class="card card-primary">
  <div class="card-header">
    <h2 class="card-title">{{ __('Historico De Almuerzos') }}</h2>
    
  </div>
  <br>
  <form method="post" action="{{route('buscar_informe')}}" enctype="multipart/form-data">
    @csrf
    <div class="box box-danger">
             <div class="box-header with-border">
                <div class="box-body">
                    <div class="row offset-md-1">
                      <div class="col-xs-3">
                         <div class="form-group col-md-9">
                          <input class="date form-control"  type="text" id="FechaI" name="FechaI"  placeholder="Fecha Inicial"  autocomplete="off" autofocus required>
                         </div>
                      </div>
     
                      <div class="col-xs-3">
                         <div class="form-group col-md-9">
                                  <input class="date form-control"  type="text" id="FechaF" name="FechaF" placeholder="Fecha Final" autocomplete="off" autofocus required>
                         </div>
                      </div>
                      <div class="col-xs-1">
                                  <div class="form-group col-md-9">
                                                  <button type="submit" class="btn btn-info pull-right">Buscar</button>
                                  </div>
                               </div>
                                                          
                    </div>
                  </div>
             </div>
            
             <!-- /.box-body -->
           </div>
           <script type="text/javascript">
             $('#FechaI').datepicker({
                 autoclose: true,
                 format: 'yyyy-mm-dd'
              });
           </script>
           <script type="text/javascript">
             $('#FechaF').datepicker({
                 autoclose: true,
                 format: 'yyyy-mm-dd'
              });
           </script>
</form>          
</div>
@endsection