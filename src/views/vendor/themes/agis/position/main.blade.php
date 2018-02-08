@foreach (@$items['page']['presentations'] as $item)
    @if($item['position'] === 'agis/position/main')
        @include('vendor.components.'.$item['component']['view'])
    @endif
@endforeach