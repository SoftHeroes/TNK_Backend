@extends('adminPanel.layout.app') @section('content')
<!-- Bet History content start -->
<main class="app-content">
    <div class="app-title">
        <div>
            <h1><i class="app-menu__icon fa fa-th-list"></i> {{__('adminPanel.providerRequestBalance')}}</h1>
            <p>{{__('adminPanel.dashboardDesc')}}</p>
        </div>
        <ul class="app-breadcrumb breadcrumb side">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item active">{{__('adminPanel.provider')}}</li>
            <li class="breadcrumb-item active"><a href="{!!route('vProviderRequestBalance')!!}">{{__('adminPanel.providerRequestBalance')}}</a></li>
        </ul>
    </div>

    @php
    $value = session(str_replace(".","_",request()->ip()).'ECGames');
    @endphp

    <div class="row">
        <div class="col-md-3">
        </div>
        <div class="col-md-6">
            <div class="tile">
                <form action="{{route('vCreditRequest')}}" method="post" enctype="multipart/form-data">
                    {{csrf_field()}}
                    <input name="version" value="0.1" hidden>
                     @if($portalProviderData == null)
                    <input name="portalProviderUUID" value="{{$value['portalProviderUUID']}}" hidden>
                    @endif
                    <div class="row">
                        <div class="col-lg-12">
                            @isset($portalProviderData)
                                @if($portalProviderData != null)
                                    <div class="form-group">
                                        <div class="form-group-select">
                                            <select class="form-control" name="portalProviderUUID" id="provider-request-balance">
                                                <option value="">{{__('adminPanel.selectPortalProvider')}}</option>
                                                @foreach ($portalProviderData as $key => $portalProvider)
                                                @if($portalProvider->PID !=1)
                                                    <option value="{{$portalProvider->UUID}}">{{$portalProvider->name}}</option>
                                                @endif
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endif
                            @endisset
                            <div class="form-group">
                                <label class="control-label">{{__('adminPanel.Amount')}}</label>
                                <div class="form-group">
                                    <label class="sr-only" for="exampleInputAmount">Amount (in dollars)</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <select class="input-group-text" id="exampleSelect1" name="currencyID">
                                                @isset($currencyData)
                                                @foreach ($currencyData as $key => $currency)
                                            <option value="{{$currency->PID}}" class="currency-{{$currency->PID}}" rate="{{$currency->rate}}">{{$currency->symbol}}</option>
                                                @endforeach 
                                                @endisset
                                            </select>
                                        </div>
                                        <input class="form-control INT" id="exampleInputAmount" onkeyup="listenRate()" name="amount" type="number" placeholder="{{__('adminPanel.Amount')}}">
                                    </div>
                                </div>
                            </div>

                            @isset($enableToEditRate)
                            <div class="form-group">
                                <label>{{__('adminPanel.rate')}}</label>
                                <input hidden type="number" id="rateValue" name="rateValue" value="{{$rate}}" />
                                @if($enableToEditRate)
                                    <input class="form-control INT" id="rate" onkeyup="listenRate()" value="{{$rate}}" min="0"  name="rate" type="number" placeholder="{{__('adminPanel.rate')}}">
                                @else
                                    <input class="form-control INT" id="rate" onkeyup="listenRate()" disabled value="{{$rate}}" min="0"  name="rate" type="number" placeholder="{{__('adminPanel.rate')}}">
                                @endif
                        </div>
                            <div class="form-group">
                                <label>{{__('adminPanel.chipValue')}}</label>
                                <input class="form-control INT" id="chipValue" disabled name="chipValue" type="number" placeholder="{{__('adminPanel.chipValue')}}">
                            </div>
                            {{-- @endif --}}
                            @endisset

                            <div class="form-group">
                                <label for="exampleInputFile">{{__('adminPanel.fileImage')}}</label>
                                <input class="form-control-file" id="exampleInputFile" name="creditRequestImage" type="file" accept="image/jpeg , image/jpg, image/gif, image/png" aria-describedby="fileHelp">
                                <!-- <small class="form-text text-muted" id="fileHelp">This is some placeholder block-level help text for the above input. It&apos;s a bit lighter and easily wraps to a new line.</small> -->
                            </div>

                            <div class="form-group">
                                <label for="comment">{{__('adminPanel.comment')}}</label>
                                <textarea class="form-control" name="creditRequestDescription" id="comment" rows="3" placeholder="{{__('adminPanel.comment')}}"></textarea>
                            </div>

                        </div>
                    </div>
                    <div class="col-lg-12">
                        <button class="btn btn-primary request-balance-submit" type="submit">{{__('adminPanel.sendRequest')}}</button>
                        <button class="btn btn-danger" type="reset">{{__('adminPanel.cancel')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
<!-- Page specific javascripts-->
<!-- Data table plugin-->
<script type="text/javascript" src="{{ asset('adminPanel/js/plugins/dataTables.bootstrap.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('adminPanel/js/inputValidation.js')}}"></script>
<script type="text/javascript">


    // X-CSRF-TOKEN
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
        }
    });

    $("#rate").on("keyup",()=>{
        $("#rate").val($("#rateValue").val());
    })

      const listenRate = () =>{
        var value = 0;
        var rate = $("#rate").val();
        var enterAmount = $("#exampleInputAmount").val();
        value = enterAmount*rate;
        $("#chipValue").val(value)
        $("#rateValue").val(rate);
      }

      $("#exampleSelect1").on("change",(value)=>{
        const currencyID = $("#exampleSelect1").val() 
        const rate = $(".currency-"+currencyID).attr("rate")
        $("#rate").val(rate);
        listenRate()
        
    })
    $('.request-balance-submit').click(function(e) {
        $(".errorField").remove();
        if ($("#provider-request-balance :selected").val() == '') {
            $('#provider-request-balance').parent().after('<span class="errorField">Select a portal provider</span>');
            e.preventDefault();
        }
        if ($("#exampleInputAmount").val() == "") {
            if ($("#exampleInputAmount").val() < 0) {
                $('#exampleInputAmount').parent().after('<span class="errorField">Please enter a value greater than or equal to 0</span>');
            } else {
                $('#exampleInputAmount').parent().after('<span class="errorField">Please enter the amount</span>');
            }
            e.preventDefault();
        }
        if ($("#comment").val() == "") {
            $("#comment").parent().after('<span class="errorField">Please enter the comments</span>');
            e.preventDefault();
        }
        if ($("#exampleInputFile").val() == "") {
            $("#exampleInputFile").parent().after('<span class="errorField">Please select a file.</span>');
            e.preventDefault();
        }
        if (($("#exampleInputFile").val() != "") && ($("#exampleInputFile")[0].files[0].size) > 2000000) {
            $("#exampleInputFile").parent().after('<span class="errorField">Please select a file less than 2 mb</span>');
            e.preventDefault();
        }
        if ($("#rate").val() == "" || $("#rate").val() < 0) {
            if ($("#rate").val() < 0) {
                $('#rate').parent().after('<span class="errorField">Please enter a value greater than or equal to 0</span>');
            } else {
                $('#rate').parent().after('<span class="errorField">Please enter the rate</span>');
            }
            e.preventDefault();
        }
        
        if ($("#chipValue").val() == "" || $("#chipValue").val() < 0) {
            if ($("#chipValue").val() < 0) {
                $('#chipValue').parent().after('<span class="errorField">Please enter a value greater than or equal to 0</span>');
            } else {
                $('#chipValue').parent().after('<span class="errorField">Please enter the chip value</span>');
            }
            e.preventDefault();
        }
    });
</script>
<!-- Bet History content end -->
@endsection