<!-- Data Source Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('data_source_id', 'Data Source Id:') !!}
    {!! Form::select('data_source_id', $datasource->pluck('name', 'id'), null, ['class' => 'form-control select2']) !!}
</div>

<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', 'Name:') !!}
    {!! Form::text('name', null, ['class' => 'form-control']) !!}
</div>

<!-- Alias Field -->
<div class="form-group col-sm-6">
    {!! Form::label('alias', 'Alias:') !!}
    {!! Form::text('alias', null, ['class' => 'form-control']) !!}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{!! route('columnAliases.index') !!}" class="btn btn-default">Cancel</a>
</div>
