@section('styles')
<style>

 #imaginary_container{
      /*  margin-top:20%; *//* Don't copy this */
        margin-bottom: 5%;
      }
      
      .stylish-input-group .input-group-addon{
          background: white !important; 
      }
      .stylish-input-group .form-control{
        border-right:0; 
        box-shadow:0 0 0; 
        border-color:#ccc;
      }
      .stylish-input-group button{
          border:0;
          background:transparent;
      }
@media (max-width: 767px) {
    /* CSS goes here */

    #imaginary_container{
      /*  margin-top:20%; *//* Don't copy this */
        /*margin-bottom: 5%;*/
        /*width: 40%;*/
        /*height: 30%;*/
      }
      
      .stylish-input-group .input-group-addon{
          background: white !important; 
      }
      .stylish-input-group .form-control{
        border-right:0; 
        box-shadow:0 0 0; 
        border-color:#ccc;
      }
      .stylish-input-group button{
          border:0;
          background:transparent;
      }
      .form-control {
        height: 45px;
      }
      .panel.panel-body{
        width: 100%;
      }
      .icon{width: 20%;}
     /* .panel.panel-default-fluid{background-color: blue;}*/
      /*.panel.panel-default {
        overflow: hidden;
        display: flex; 
        position: relative;
        z-index: 1;
      }*/
}
  
</style>
@endsection

{{--<div class="row">
        <div class="col-sm-6 col-sm-offset-3" style="padding-top: 5px">
            <div id="imaginary_container" style="display: none"> 
                <div class="input-group stylish-input-group">
                    <input type="text" class="form-control"  placeholder="Search" id="search-criteria" >
                    <span class="input-group-addon">
                        <button type="submit" id="search">
                            <span class="glyphicon glyphicon-search"></span>
                        </button>  
                    </span>
                </div>
            </div>
        </div>
  </div>--}}

@foreach ($data as $no=>$item)
@if ($item['slug'] !== 'tasks' && $item['slug'] !== 'views')
{{--dd($item['slug'])--}}
<div class="container">
<div class="col-sm-12 center">
<a  href="{{ url('page/'.$item['slug']) }}" style="color:#696969; font-size: 20px; display: block;">
<div class="panel panel-default" data-eventtype="{{$item['title']}}">
  <div class="panel-body" style="text-align: center;font-family: 'Lato';">

      <div class="panel-title pull-left">
            {{$item['title']}}
         </div>
      <div class="panel-title pull-right">
      <i class="fa fa-angle-right fa-2x" aria-hidden="true" style="color: #696969;"></i>
      </div>
     
  </div>
</div>
 </a>
 </div>

 </div>
@endif
@endforeach

@section('scripts')
<script>
$(document).ready(function($) {
$('.panel.panel-default').each(function(element) {
        var dt = $(this).data('eventtype').toLowerCase();
        $(this).attr('data-eventtype',dt);
  });

});

$('#search').click(function(){
    $('.panel-default').hide();
    var search = $('#search-criteria').val().toLowerCase();
    //var att = $('.panel.panel-default[data-eventtype*="'+search+'"]').show();
    $('.panel.panel-default[data-eventtype^="'+search+'"]').show();
});

/*
 $('#search').click(function(){
    $('.panel.panel-default').hide();
   var txt = $('#search-criteria').val();
   console.log(txt)
   //$('.panel-body:contains("'+txt+'")').show();
   $('.panel.panel-default[data-eventtype="'+txt+'"]').show();
});
*/
</script>
@endsection