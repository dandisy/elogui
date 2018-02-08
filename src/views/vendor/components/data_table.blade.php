@section('styles')
    @include('layouts.datatables2_css')
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

<style type="text/css">
    .dt-buttons.btn-group{
        display: none;
    }

    .pagination>.active>a,
    .pagination>.active>a:focus,
    .pagination>.active>a:hover,
    .pagination>.active>span,
    .pagination>.active>span:focus,
    .pagination>.active>span:hover {
        z-index: 3;
        color: #fff;
        cursor: default;
        background-color: #017d78;
        border-color:#017d78;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0;
    }

    .table>thead:first-child>tr:first-child>th {
        border-top: 0;
        color:#fff;
        background-color: #017d78;
    }

    .dataTables_info {
        clear: both;
        /* float */
        padding-top: 0.755em;
    }

    .dataTables_paginate .paging_simple_numbers{
        text-align: left;
    }

    table.dataTable tfoot th,
    table.dataTable tfoot td {
        background-color: #017d78;
    }
 
    div.dataTables_filter{
        text-align: center;

        margin-top: 5px;
        margin-top: 5px;
        /*display:none;*/
    }
    .dataTables_filter >label {
        /*display: none;*/
    }

    .pagination>li>a,
    .pagination>li>span {
        position: relative;
        float: left;
        padding: 6px 12px;
        margin-left: -1px;
        line-height: 1.42857143;
        /*color: rgb(241, 126, 21);*/
        color:#017d78;
        text-decoration: none;
        background-color: #fff;
        border: 1px solid #ddd;
    }

    /*modal*//*;color: rgb(241, 126, 21)*/
    .modal-header.filter{background-color: #000;color: #c9c3c3;}
    .modal-header.filter > button.close {
        -webkit-appearance: none;
        padding: 0;
        cursor: pointer;
        color:#c9c3c3;
        border: 0;
    }
    .modal-header.filter .modal-title {
        top: 10px;
        right: 0;
        bottom: 0;
        left: 0; }
    .modal-body {
        padding: 0;
        /*position: absolute;*/
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
    }
    table.dataTable th.dt-center,
    table.dataTable td.dt-center,
    table.dataTable td.dataTables_empty {
        text-align: left;
    }
    .select2-container .select2-selection--single {
        height: 33px;
    }
    .select2-container--default .select2-selection--single {
        padding: 6px;
        color: #555;
        background-color: #fff;
        background-image: none;
        border: 1px solid #ccc;
        border-radius: 4px;
        -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
        box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
        -webkit-transition: border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s;
        -o-transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
        transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 31px;
    }
    .column-math li {
        cursor: pointer;
    }
</style>

<h3 align="center">{{ $items['page']['presentations'][0]['component']['data_source']['description'] }}</h3>

{!! $dataTable->table(['class' => 'table table-bordered', 'width' => '100%'], true) !!}

<!-- modal filter -->
@if(!empty($items['data']))
    @include('vendor.components.filterBydate')
@endif

@section('scripts')
    @include('layouts.datatables_js')
    {!! $dataTable->scripts() !!}

    <script src="http://cdn.datatables.net/plug-ins/1.10.16/dataRender/datetime.js"></script>
    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>

    <script>
        var columnsData = {!! json_encode($items['dataTable']['columnsUniqueData']) !!};

        var titleTemp = [];
        $('#dataTableBuilder').append('<div></div>');
        $('#dataTableBuilder thead').append('<tr class="column-search" style="background-color:#b3cccc;"></tr>');
        //var selecttr = $('#dataTableBuilder tfoot').append('<tr class="column-search-select"></tr>');
        $('#dataTableBuilder thead th').each(function() {
            var title = $(this).text();
            titleTemp.push(title);
            if(title === 'Action') {
                $('.column-search').append('<td></td>');
            }
            else {
                $('.column-search').append('<td id="'+title+'"><input class="form-control" type="text" placeholder="Search '+title+'" /></td>');
            }
        });

        // datatable select2 column search
        $.each(columnsData, function (index, item) {
            var itemArray = Object.keys(item).map(function(x) { return item[x]; });
            if (itemArray.length <= 30 && itemArray.length > 0){
                $('#'+index).empty();
                $('#'+index).append('<select class="form-control select2" style="width:100%"><select>');

                var option = '<option></option>';
                $.each(item, function (idx, itm) {
                    option += '<option value="'+itm+'">'+itm+'</option>';
                });

                 var el = $('#'+index+' select').append(option);

                 el.select2();
            }
        });
        // end datatable select2 column search

        // handling column math
        var allColumnData = {!! json_encode($items['data']->first()->toArray()) !!};

        $(document).on('click', '.column-math li', function (e) {
            e.preventDefault();

            var value = $(this).find('a').attr('class');
            var column = $(this).parents('.column-math').data('column');

            var getArrayColumn = allColumnData.map(function(i){
                return i[column];
            });

            $(this).parents('.column-math').find('.column-math-input').val(columnMath(getArrayColumn, value));
        });

        var columnMath = function(arr, r) {
            var sum = 0;
            var count = 0;
            var max = 0;
            var min;

            for(var i = 0; i < arr.length; i++) {
                if (arr[i].match(/^[0-9]+$/) !== null) {
                    sum += parseInt(arr[i]);
                    count++;
                    max = parseInt(arr[i]) > max ? parseInt(arr[i]) : max;
                    if(i === 0) {
                        min = parseInt(arr[i]);
                    } else {
                        min = parseInt(arr[i]) > min ? min : parseInt(arr[i]);
                    }
                }
            }

            if(r === 'count') {
                return count;
            } else if(r === 'avg') {
                return sum/count;
            } else if(r === 'max') {
                return max;
            } else if(r === 'min') {
                return min;
            } else {
                return sum;
            }
        };

        $('#dataTableBuilder tfoot tr').empty();

        $.each(columnsData, function(index, item) {
            var columnTypeNum = false;
            var uniqueItemArray = Object.keys(item).map(function(x) { return item[x]; });

            $.each(uniqueItemArray, function(idx,itm) {
                var isItemNum = /^[0-9]+$/.test(itm);

                if(isItemNum) {
                    columnTypeNum = true;
                }
            });

            if(columnTypeNum){
                $('#dataTableBuilder tfoot tr').append(`
                    <th>
                        <div class="input-group column-math" data-column="`+index+`">
                            <div class="input-group-btn dropup">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Math <span class="caret"></span></button>
                                <ul class="dropdown-menu">
                                    <li><a class="sum">SUM</a></li>
                                    <li><a class="count">COUNT</a></li>
                                    <li><a class="avg">AVG</a></li>
                                    <li><a class="max">MAX</a></li>
                                    <li><a class="min">MIN</a></li>
                                </ul>
                            </div>
                            <input type="text" class="form-control column-math-input" placeholder="Column math">
                        </div>
                    </th>
                `);
            } else {
                $('#dataTableBuilder tfoot tr').append('<th></th>');
            }
        });
        // end hanling column math

        // $('.column-sum').append('<td><select class="form-control"><option ></option><option value="sum" >SUM</option><option value="avg" >AVG</option><option value="max" >MAX</option><option value="min" >MIN</option></select>'+titleTemp[titleIndex]+'</td>');
        // titleIndex += 1;

        //$('.column-sum').append('<td><select class="form-control"><option ></option><option value="sum" >SUM</option><option value="avg" >AVG</option><option value="max" >MAX</option><option value="min" >MIN</option></select></td>')

        //  $('#dataTableBuilder tfoot th').each(function() {
        //     var title = $(this).text();
        //     if(title === 'Action') {
        //         $('.column-search-select').append('<td></td>');
        //     } else {
        //          var select=$('.column-search-select').append('<td><select><option value=""></option></select></td>');
        //          // .on( 'change', function () {
        //          //        var val = $.fn.dataTable.util.escapeRegex(
        //          //            $(this).val();
        //          //        });

        //     }
        //     console.log(select)
        // });

        var table = $('#dataTableBuilder').DataTable();
    
        var idx = 0;
        table.columns().every(function() {
            var that = this;
    
            $('input.column-search', $('.column-search td').get(idx)).on('keyup change', function() {
                if (that.search() !== this.value) {
                    that
                        .search(this.value)
                        .draw();
                }
            });
			
			$('select.column-search', $('.column-search td').get(idx)).on('keyup change', function() {
                if (that.search() !== this.value) {
                    that
                        .search(this.value)
                        .draw();
                }
            });

            idx++;
        });
        
        $('#dataTableBuilder').wrap('<div class="table-responsive"></div>');
        $('.table-responsive').before('<div class="clearfix"></div>');
    </script>
@endsection