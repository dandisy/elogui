<div style="margin-bottom:15px">
  <span class="fa fa-calendar"></span> Filter
</div>

@foreach ($data as $item)
@if ($item['slug'] !== 'tasks' && $item['slug'] !== 'views')
<div class="panel panel-default">
  <div class="panel-body">
      <a class="btn btn-primary btn-block" href="{{ url('page/'.$item['slug']) }}">{{$item['title']}}</a>
  </div>
</div>
@endif
@endforeach