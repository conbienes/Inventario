@extends("theme.$theme.layout")

@section('content')

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>


  <!-- Modal -->
  <div class="modal fade" id="myModal"  >
    <div class="modal-dialog ">
      <div class="modal-content" >
        <div class="modal-header" style="background-color:#5bc0de" >
          <h5 >Calificar Entrega de los productos</h5>
        </div>

        <form method="POST" action="{{ route('CalificarPost') }}">
          @csrf
            <table ALIGN="center">
              <tr >
                <td>
                    <p class="clasificacion">
                      <input id="radio1" type="radio" name="estrellas" id="estrellas" value="1" required>
                      <label for="radio1">★</label>
                      <input id="radio2" type="radio" name="estrellas" id="estrellas" value="2">
                      <label for="radio2">★</label>
                      <input id="radio3" type="radio" name="estrellas" id="estrellas" value="3">
                      <label for="radio3">★</label>
                      <input id="radio4" type="radio" name="estrellas" id="estrellas"  value="4">
                      <label for="radio4">★</label>
                      <input id="radio5" type="radio" name="estrellas" id="estrellas" value="5">
                      <label for="radio5">★</label>
                    </p>
                </td>
              </tr>
              <tr>
                <td>
                    <textarea name="CCalificacion" id="CCalificacion" maxlength="200"  class="form-control" rows="3" autocomplete="off" required></textarea>
                </td>
              </tr>
              <tr>
                <td>
                  <input name="registroF" type="hidden" id="registroF" value="{{$registroF}}"></textarea>
                  </td>
                </tr>
            </table>
                    <br>
          <table class="table" >
            <tr ALIGN="center">
            <td >
              <div class="form-group" >
              <button type="submit" class="btn btn-success">{{ __('Calificar') }}</button>
              </div>
            </td>
          </tr>
        </table>

      </form>


    </div>
  </div>
</div>

  <script>
    $( document ).ready(function() {
        $('#myModal').modal({  backdrop: 'static',
        keyboard: false}).modal('show')
    });
    </script>

    <script>
        $(".clasificacion").find("input").change(function(){
        var valor = $(this).val()
        $(".clasificacion").find("input").removeClass("activo")
        $(".clasificacion").find("input").each(function(index){
          if(index+1<=valor){
            $(this).addClass("activo")
          }

        })
      })

      $(".clasificacion").find("label").mouseover(function(){
        var valor = $(this).prev("input").val()
        $(".clasificacion").find("input").removeClass("activo")
        $(".clasificacion").find("input").each(function(index){
          if(index+1<=valor){
            $(this).addClass("activo")
          }

        })
      })
    </script>

    <style>
      .clasificacion input[type='radio'] {
    opacity: 0;
      }
      .clasificacion label {
        font-size: 30px;
        color:rgb(150, 150, 150);
        cursor: pointer;
      }
      .clasificacion label:hover {
        color: rgb(217, 215, 11);
      }
      .activo + label{
      color: rgb(255, 230, 0) !important;
      }
    </style>
@endsection








