<article class="col-sm-9">
    @foreach (@$items['page']['presentations'] as $item)
        @if($item['position'] === 'agis/position/main_right')
            @include('vendor.components.'.$item['component']['view'])
        @endif
    @endforeach
</article>