
@extends("theme.$theme.layout")

@section('content')


@include('Imprimir.index',[
  'restaurante'=>$restaurante,
  'empleado'=>$empleado,
    'ticket'=>$ticket 
])

@include('Inicio.index',['restaurante'=>$restaurantes,
'mensaje'=>'Se ha registrado con éxito tu ticket, buen provecho.']))

@endsection