<div class="modal fade" id="myModal2" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div id="form_status">
                <div class="modal-header filter">
                    <a href="#" style="color: rgb(241, 126, 21); font-size:15px;">Reset</a>
                    <button type="button" class="close filter" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" style="color:#ffffff" align="center">Search & Filter</h4>
                </div>

                <div class="modal-body">
                    <div class="modal-enter" style="background-color: #eeeeee; padding:15px;">
                        <p style="font-size: 20px; color:#000000; " >Enter Keyword</p>
                    </div>


                    <form id="form-filter" action="{{url('/cekfilter')}}" method="POST">
                        <div class="form-group col-xs-12 col-lg-12">
                            <div class="col-xs-6">
                                <label>Start Date</label>
                                <input type="date" name="startDate">
                            </div>
                            <div class="col-xs-6">
                                <label>End Date</label>
                                <input type="date" name="endDate">
                            </div>
                        </div>

                        @if($items['data']->first())
                        @foreach ($items['data']->first() as $key => $val)
                        <table style="width:100%;">
                            <th>
                                <div style="border-bottom:1px solid #d9d9d9; padding:20px 20px 20px;  width:100%;text-transform: uppercase;">
                                    <input type="checkbox" name="{{$key}}" value="{{$key}}"/> {{$key}}
                                </div>
                            </th>
                        </table>
                        @endforeach
                        @endif
                    </form>
                </div>

                <button type="Submit" class="btn btn-warning" style="width: 100%; border-radius:0px;" >SUBMIT</button>
            </div>
        </div>
    </div>
</div>

@section('script')
<script>
    $(document).ready(function(){
        //modal
        $('.modal-filter').click(function() {
            $("#myModal2").modal();
        });

        // console.log( $('button.close.filter'))
    });
</script>
@endsection