<!-- Data Source Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('data_source_id', 'Data Source Id:') !!}
    {!! Form::select('data_source_id', $datasource->pluck('name', 'id'), null, ['class' => 'form-control select2']) !!}
</div>

<!-- Parent Field -->
<div class="form-group col-sm-6">
    {!! Form::label('parent', 'Parent:') !!}
    {!! Form::select('parent', $dataquery->pluck('id', 'id'), null, ['class' => 'form-control select2']) !!}
</div>

<!-- Command Field -->
<div class="form-group col-sm-6">
    {!! Form::label('command', 'Command:') !!}
    {!! Form::select('command', ['get' => 'get', 'latest' => 'latest', 'first' => 'first', 'orderBy' => 'orderBy', 'orderByRaw' => 'orderByRaw', 'inRandomOrder' => 'inRandomOrder', 'offset' => 'offset', 'limit' => 'limit', 'select' => 'select', 'addSelect' => 'addSelect', 'selectRaw' => 'selectRaw', 'where' => 'where', 'whereNull' => 'whereNull', 'whereNotNull' => 'whereNotNull', 'whereIn' => 'whereIn', 'whereNotIn' => 'whereNotIn', 'orWhere' => 'orWhere', 'whereBetween' => 'whereBetween', 'whereNotBetween' => 'whereNotBetween', 'whereDate' => 'whereDate', 'whereMonth' => 'whereMonth', 'whereDay' => 'whereDay', 'whereYear' => 'whereYear', 'whereTime' => 'whereTime', 'whereColumn' => 'whereColumn', 'whereExists' => 'whereExists', 'whereRaw' => 'whereRaw', 'join' => 'join', 'leftJoin' => 'leftJoin', 'crossJoin' => 'crossJoin', 'on' => 'on', 'orOn' => 'orOn', 'groupBy' => 'groupBy', 'havingRaw' => 'havingRaw', 'orHavingRaw' => 'orHavingRaw', 'value' => 'value', 'pluck' => 'pluck', 'count' => 'count', 'max' => 'max', 'avg' => 'avg', 'union' => 'union', 'chunk' => 'chunk', 'when' => 'when'], null, ['class' => 'form-control select2']) !!}
</div>

<!-- Column Field -->
<div class="form-group col-sm-6">
    {!! Form::label('column', 'Column:') !!}
    {!! Form::select('Column', ['field1' => 'field1', 'field2' => 'field2', 'field3' => 'field3'], null, ['class' => 'form-control select2', 'multiple]) !!}
</div>

<!-- Operator Field -->
<div class="form-group col-sm-6">
    {!! Form::label('operator', 'Operator:') !!}
    {!! Form::select('operator', ['=' => '=', '>' => '>', '<' => '<', '>=' => '>=', '<=' => '<=', '!=' => '!=', 'NULL' => 'NULL', 'NOT NULL' => 'NOT NULL', 'LIKE' => 'LIKE', '%LIKE%' => '%LIKE%'], null, ['class' => 'form-control select2']) !!}
</div>

<!-- Value Field -->
<div class="form-group col-sm-6">
    {!! Form::label('value', 'Value:') !!}
    {!! Form::text('value', null, ['class' => 'form-control']) !!}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{!! route('dataQueries.index') !!}" class="btn btn-default">Cancel</a>
</div>
