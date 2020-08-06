@extends('adminPanel.layout.app')
@section('content')
<!-- User Profile content start -->
<div class="modal fade" id="modalTC" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">{{ __('adminPanel.bettingStatistics') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body betStatistics">
                <p class="bet-status"></p>
                <p class="bet-amount"></p>
                <p class="rolling-amount"></p>
                <p class="payout"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<main class="app-content">
    <div class="app-title">
        <div>
            <h1><i class="app-menu__icon fa fa-th-list"></i>{{ __('adminPanel.userProfile') }}</h1>
            <p>{{ __('adminPanel.dashboardDesc') }}</p>
        </div>
        <ul class="app-breadcrumb breadcrumb side">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            @isset($userBasicInfo)
            <li class="breadcrumb-item active"><a href="{{ route('vUserProfile', ['userUUID' => base64_encode($userBasicInfo->userUUID)]) }}">{{__('adminPanel.userProfile')}}</a></li>
            @endif
        </ul>
    </div>
    <div class="row user">
        <div class="col-md-12">
            <div class="profile">
                @isset($userBasicInfo)
                <div class="info">
                    <img class="user-img" src="{{ asset($userBasicInfo->profileImage) }}">
                    <h4>{{ $userBasicInfo->firstName }} {{ $userBasicInfo->lastName }}</h4>
                    <p>
                        {{ __('adminPanel.userID') }}: {{ $userBasicInfo->userUUID }}<br>
                        {{ __('adminPanel.username') }}: {{ $userBasicInfo->userName }}<br>

                        @if ($userBasicInfo->email != "")
                            {{ __('adminPanel.emails') }}: {{ $userBasicInfo->email }}<br>
                        @endif

                        @if ($userBasicInfo->currentActiveTime == "offline")
                            {{ __('adminPanel.onlineStatus') }}:   <span class="badge badge-danger">{{ __('adminPanel.isOffline') }}</span><br>
                        @else
                            {{ __('adminPanel.onlineStatus') }}: <span class="badge badge-success">{{ __('adminPanel.isOnline') }}</span> <br> ({{$userBasicInfo->currentActiveTime}})
                        @endif

                    </p>
                    <h5><a href="{{route('vUserOnlineHistory', ['userUUID' => base64_encode($userBasicInfo->userUUID)])}}" id="getURL" class="user-online-history">{{__('adminPanel.userOnlineHistory')}}</a></h4>
                </div>
                @endif
                <div class="cover-image"></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="tab-content">
                <div class="tab-pane active" id="">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="tile">
                                <h4>{{ __('adminPanel.totalWinningValue') }}</h4><br>
                                <div class="tile-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered" id="">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('adminPanel.totalNumberOfBets') }}</th>
                                                    <th>{{ __('adminPanel.winPercentage') }}</th>
                                                    <th>{{ __('adminPanel.lossPercentage') }}</th>
                                                    <th>{{ __('adminPanel.amountEarned') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @if( (isset($winLossValue)) && (count($winLossValue) > 0) )
                                                <tr>
                                                    <td>{{ $winLossValue[0]->totalBets }}</td>
                                                    <td>{{ $winLossValue[0]->winRate }}</td>
                                                    <td>{{ $winLossValue[0]->lossRate }}</td>
                                                    <td>{{ $winLossValue[0]->totalProfitEarned }}</td>
                                                </tr>
                                            @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="tab-content">
                <div class="tab-pane active" id="user-timeline">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="tile">
                                <h4>{{ __('adminPanel.bettingInformation') }}</h4><br>
                                <div class="tile-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered" id="sampleTable">
                                            <thead>
                                                <tr>
                                                    <th> {{ __('adminPanel.betId') }} </th>
                                                    <th> {{ __('adminPanel.ruleName') }} </th>
                                                    <th> {{ __('adminPanel.betResult') }} </th>
                                                    <th> {{ __('adminPanel.gameID') }} </th>
                                                    <th> {{ __('adminPanel.stockName') }} </th>
                                                    <th> {{ __('adminPanel.gameStartDateTime') }} </th>
                                                    <th> {{ __('adminPanel.endDateTime') }} </th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<!-- Page specific javascripts-->
<!-- Data table plugin-->
<script type="text/javascript" src="{{ asset('adminPanel/js/plugins/dataTables.bootstrap.min.js')}}"></script>
<script type="text/javascript">
$(document).ready(function(){
    var url = $('#getURL').val();
    var table = $('#sampleTable').DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        dom: "<'row'<'col-sm-5'l><'col-sm-7'f>>"+"<'row'<'col-sm-12'tr>>"+"<'row'<'col-sm-5'i><'col-sm-7'p>>",
        language:{
            search: "_INPUT_",
            searchPlaceholder: "{{__('adminPanel.search')}}"
        },
        ajax: url,
        columns:[
            { title: "@lang('adminPanel.betId')", data: "betUUID", name:"betting.UUID" },
            { 
                title: "@lang('adminPanel.ruleName')", 
                data: "ruleName",
                render: function(data){
                        var ruleName;
                            if (data.split('_')[0] == 'FD' ) ruleName = data.replace('FD_', 'FIRST_DIGIT_');
                            else if (data.split('_')[0] == 'LD' ) ruleName = data.replace('LD_', 'LAST_DIGIT_');
                            else if (data.split('_')[0] == 'TD' ) ruleName = data.replace('TD_', 'TWO_DIGIT_');
                            else if (data.split('_')[0] == 'BD' ) ruleName = data.replace('BD_', 'BOTH_DIGIT_');
                            else ruleName = "";
                        return ruleName;
				},
                name: "rule.name"
            },
            { 
                title: "@lang('adminPanel.betResult')", 
                data: "betResult",
                render: function(betResult){
                       var className;
                            if (betResult == "lose" ) { className = 'badge-danger'; betResult = "@lang('adminPanel.lose')"}
                            else if (betResult == "win" ) { className = 'badge-success'; betResult = "@lang('adminPanel.win')"}
                            else { className = 'badge-warning'; betResult = "@lang('adminPanel.pending')"}
                        return "<span class='badge "+className+"'>"+betResult+"</span>";
				} ,
                name:'betting.betResult'
            },
            { title: "@lang('adminPanel.gameID')", data: "gameUUID", name:"game.UUID" },
            { title: "@lang('adminPanel.stockName')", data: "stockName", name:"stock.name" },
            { title: "@lang('adminPanel.gameStartDateTime')", data: "gameStartDateTime", name:"game.startDate" },
            { title: "@lang('adminPanel.endDateTime')", data: "gameEndDateTime", name:"game.endDate" },
            {title: "@lang('adminPanel.betStatus')", data: 'action', name: 'action', orderable: false, searchable: false} 
        ]
    });

       // To store the label information from the language files
       var betStatusLabel = @json( __('adminPanel.betStatus') );
        var betAmountLabel = @json( __('adminPanel.betAmount') );
        var RollingAmountLabel = @json( __('adminPanel.rollingAmount') );
        var payoutLabel = @json( __('adminPanel.payout') );
        var pending = @json( __('adminPanel.pending') );
        var win = @json( __('adminPanel.win') );
        var lose = @json( __('adminPanel.lose') );

        $('#modalTC').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget) // Button that triggered the modal
            var recipients = button.data('todo') // Extract info from data-* attributes
            recipient = recipients.split(',');
            
            // Update the modal's content.
            var modal = $(this)
            if (recipient[0] == "pending") {
                modal.find('.betStatistics .bet-status').html('<strong>'+betStatusLabel+': </strong><span class="badge badge-warning">' + pending + '</span>')
            } else if (recipient[0] == "win") {
                modal.find('.betStatistics .bet-status').html('<strong>'+betStatusLabel+': </strong><span class="badge badge-success">' + win + '</span>')
            } else {
                modal.find('.betStatistics .bet-status').html('<strong>'+betStatusLabel+': </strong><span class="badge badge-danger">' + lose + '</span>')
            }
            modal.find('.betStatistics .bet-amount').html('<strong>'+betAmountLabel+': </strong>' + recipient[1])
            modal.find('.betStatistics .rolling-amount').html('<strong>'+RollingAmountLabel+': </strong>' + recipient[2])
            modal.find('.betStatistics .payout').html('<strong>'+payoutLabel+': </strong>' + recipient[3])
        });

});
</script>

<!-- game History content end -->
@endsection
