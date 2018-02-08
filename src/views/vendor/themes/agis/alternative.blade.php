@extends('vendor.themes.agis.master')

@section('content')
  <div class="col-sm-12">
    @include('vendor.themes.agis.position.top')
  </div>

  @include('vendor.themes.agis.position.left')

  @include('vendor.themes.agis.position.main_right')

  <div class="col-sm-12">
    @include('vendor.themes.agis.position.main')
  </div>
@endsection