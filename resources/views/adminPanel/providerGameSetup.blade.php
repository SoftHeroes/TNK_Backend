@extends('adminPanel.layout.app') @section('content')
<!-- Bet History content start -->
<main class="app-content">
    <div class="app-title">
        <div>
            <h1>
                <i class="app-menu__icon fa fa-th-list"></i> {{__('adminPanel.providerGameSetup')}}
            </h1>
            <p>{{__('adminPanel.dashboardDesc')}}</p>
        </div>
        <ul class="app-breadcrumb breadcrumb side">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item active">{{__('adminPanel.provider')}}</li>
            <li class="breadcrumb-item active">
                <a href="{!!route('vProviderGameSetup')!!}">{{__('adminPanel.providerGameSetup')}}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="tile">
                <div class="tile-body">
                    <div class="form-group col-md-4">
                        @isset($StockData)
                        <div class="col-md-6 form-group-select">
                            <select class="form-control" id="stockSelect" >
                                <option value="">{{__('adminPanel.selectStock')}}</option>
                                @foreach ($StockData as $key => $stock)
                                <option value="{{$stock->PID}}">{{$stock->name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 form-group-select selectPayout" style="display: none;">
                        <label> {{__('adminPanel.payoutType')}} <label>
                            <select class="form-control optionselected" id="payoutID" ></select>
                        </div>
                        @endisset

                        @isset($portalProviderData)
                        @if($portalProviderData != null)
                        <div class="col-md-6 form-group-select">
                            <select class="form-control" id="selectPortalProvider">
                                <option value="">{{__('adminPanel.selectPortalProvider')}}</option>
                                @foreach ($portalProviderData as $key => $portalProvider)
                                @if($portalProvider->PID !=1)
                                <option value="{{$portalProvider->UUID}}">{{$portalProvider->name}}</option>
                                @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 form-group-select stockSelect" style="display: none;">
                                 <select class="form-control optionPortalProviderSelected" id="stockSelects">
                                    <option value="">{{__('adminPanel.selectStock')}}</option>
                                 </select>
                        </div>

                        <div class="col-md-6 form-group-select selectPayout" style="display: none;">
                            <label> {{__('adminPanel.payoutType')}} <label>
                                <select class="form-control optionselected" id="payoutID"></select>
                            </div>

                        @endif
                        @endisset
                    </div>

                    <div class="d-flex col-md-12">
                        <div class="col-md-2"></div>
                        <div class="col-md-8">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered">
                                    <thead>
                                        <tr class="text-center">
                                            <th>{{__('adminPanel.games')}}</th>
                                            {{-- <th>{{__('adminPanel.payout')}}</th> --}}
                                            <th>{{__('adminPanel.initial')}}</th>
                                            <th>{{__('adminPanel.commission')}}</th>
                                            <th>{{__('adminPanel.gameLoop')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="myTable"></tbody>
                                </table>
                            </div>
                            <div class="col-md-3"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Data table plugin-->
<script type="text/javascript" src="{{ asset('adminPanel/js/plugins/dataTables.bootstrap.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('adminPanel/js/plugins/sweetalert.min.js')}}"></script>

<script>
 $(document).ready(function(){

    $("#selectPortalProvider").on("change", function() {
            var portalProviderUUID = $('#selectPortalProvider').val();
            portalProviderSelect(portalProviderUUID);
            $(".selectPayout").css('display', 'none')
    });

    $("#stockSelect,#stockSelects").on("change", function() {
        // check admin or user portalProvider for Select stock
        if ($('#stockSelect').val() == undefined) {
            var stockid = $('#stockSelects').val();
            var portalProviderUUID = $('#selectPortalProvider').val();
        } else {
            var stockid = $('#stockSelect').val();
            var portalProviderUUID = null;
        }

            stockSelect(stockid,portalProviderUUID);

    });

    $("#payoutID").on("change", function() {
        if ($('#stockSelect').val() == undefined) {
            var stockid = $('#stockSelects').val();
            var portalProviderUUID = $('#selectPortalProvider').val();
        } else {
            var stockid = $('#stockSelect').val();
            var portalProviderUUID = null;
        }
            var payoutid = $('#payoutID').val();
            updatepayout(stockid, payoutid,portalProviderUUID);
    });

    // X-CSRF-TOKEN
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
        }
    });

    function portalProviderSelect(portalProviderUUID){
        $(".re").remove();
        $(".sr").remove();

        // check portalProviderUUID
        if(portalProviderUUID != ''){
            $(".stockSelect").css('display', 'block')
        }else{
            $(".stockSelect").css('display', 'none')
        }
        //send PortalProviderSelect post
                $.ajax({
                    url: "{{ route('PortalProviderSelect') }}",
                    method: "post",
                    data: {
                        portalProviderUUID: portalProviderUUID
                    },
                    success: result => {
                        $.each(result.data, function(index, item) {
                            $(".optionPortalProviderSelected").append("<option class='sr' value='"+item.PID+"'>"+item.name+"</option>");
                        });
                    }
                });
    }

    // chheck stock Select
    function stockSelect(stockid,portalProviderUUID) {
        $(".re").remove();
        var nameRule = ['First Digit Big Small Game',
                'First Digit Even Odd Game',
                'First Digit Low Middle High Game',
                'First Digit Number Game',
                'Last Digit Big Small Game',
                'Last Digit Even Odd Game',
                'Last Digit Low Middle High Game',
                'Last Digit Number Game',
                'Two Digit Big Small Tie Game',
                'Two Digit Even Odd Game',
                'Two Digit Low Middle High Game',
                'Two Digit Number Game',
                'Both Digit Big Small Tie Game',
                'Both Digit Even Odd Game',
                'Both Digit Low Middle High Game',
                'Both Digit Number Game'
                ];

                if(stockid != ''){
                    $(".selectPayout").css('display', 'block')
                }else{
                    $(".selectPayout").css('display', 'none')
                }

            if(stockid != ''){
                $.ajax({
                    url: "{{ route('ProviderGameSetup') }}",
                    method: "post",
                    data: {
                        txtStockID: stockid,portalProviderUUID: portalProviderUUID
                    },
                    success: result => {
                        if(result.data.rule <= 0){
                            $(".myTable").html('<tr><td colspan=2><div class="bs-component"><div class="alert alert-dismissible alert-warning">Sorry, No data to dispaly</div></div></td></tr>');
                        }else{
                             if(result.data.payout == 1){
                                $('.optionselected').html("<option value='1' selected='selected'>@lang('adminPanel.standard')</option> <option value='2'>@lang('adminPanel.dynamic')</option>");
                            }else{
                                $('.optionselected').html("<option value='1'>@lang('adminPanel.standard')</option> <option value='2' selected='selected'>@lang('adminPanel.dynamic')</option>");
                            }
                        $.each(result.data.rule, function(index, item) {
                            // <td>" + item.PID +"</td>
                            $(".myTable").append("<tr class='re'><td>"+ nameRule[index] +"</td><td>"+ item.initialOdd +"</td><td>" + item.commission +"</td><td>" + item.gameLoop +"</td></tr><tr>");

                        });
                        }
                    }
                });
            }
    }

    // chheck update payout type
    function updatepayout(stockid, payoutid,portalProviderUUID) {
            swal({
      		title: "{{__('adminPanel.areYouSure')}}",
      		text: "{{__('adminPanel.youWantToChangePayOutType')}}",
      		type: "warning",
      		showCancelButton: true,
      		confirmButtonText: "{{__('adminPanel.YesUpdate')}}",
      		cancelButtonText: "{{__('adminPanel.NoCancel')}}",
      		closeOnConfirm: false,
      		closeOnCancel: false
      	    }, function(isConfirm) {
                  // chheck Confirm update or not
      		    if (isConfirm) {
                    $.ajax({
                    url: "{{ route('updateProviderPayout') }}",
                    method: "post",
                    data: {
                        txtStockID:stockid, txtPayoutID:payoutid, portalProviderUUID:portalProviderUUID
                    },
                    success: result => {
                       if(result.status){
                        // "Payout type updated successfully."
                            swal({title:"{{__('adminPanel.updateSuccess')}}", text:result.data.msg, type: "success", timer:1500, showConfirmButton: false});
                            stockSelect(stockid,portalProviderUUID);
                       }
                    }
            });
      		} else {
      			swal({title:"{{__('adminPanel.Canceled')}}", text:"{{__('adminPanel.payoutTypeNotChanged')}}", type: "error",timer:1500, showConfirmButton: false});
                stockSelect(stockid,portalProviderUUID);
      		}
      	});


    }
});
</script>
@endsection
