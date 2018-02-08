<div class="top-content" style="position:relative">
    @include('vendor.themes.agis.position.top_left_float')
    
    @foreach (@$items['page']['presentations'] as $item)
        @if($item['position'] === 'agis/position/top')
            @include('vendor.components.'.$item['component']['view'])
        @endif
    @endforeach
</div>