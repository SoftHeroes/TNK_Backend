@extends('adminPanel.layout.app')
@section('content')
<!-- Bet History content start -->
<main class="app-content">
    <div class="app-title">
        <div>
            <h1><i class="app-menu__icon fa fa-th-list"></i>{{__('adminPanel.betHistory')}}</h1>
            <p>{{__('adminPanel.dashboardDesc')}}</p>
        </div>
        <ul class="app-breadcrumb breadcrumb side">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item active"><a href="{!!route('vBetHistory')!!}">{{__('adminPanel.betHistory')}}</a></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="tile">
                <div class="tile-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="sampleTable">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>{{__('adminPanel.userID')}}</th>
                                    <th>{{__('adminPanel.betId')}}</th>
                                    <th>{{__('adminPanel.portalProviderID')}}</th>
                                    <th>{{__('adminPanel.portalProviderName')}}</th>
                                    <th>{{__('adminPanel.ruleName')}}</th>
                                    <th>{{__('adminPanel.betAmount')}}</th>
                                    <th>{{__('adminPanel.rollingAmount')}}</th>
                                    <th>{{__('adminPanel.payout')}}</th>
                                    <th>{{__('adminPanel.betResult')}}</th>
                                    <th>{{__('adminPanel.createdTime')}}</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr>
                                    <th></th>
                                    <th>{{__('adminPanel.userID')}}</th>
                                    <th>{{__('adminPanel.betId')}}</th>
                                    <th>{{__('adminPanel.portalProviderID')}}</th>
                                    <th>{{__('adminPanel.portalProviderName')}}</th>
                                    <th>{{__('adminPanel.ruleName')}}</th>
                                    <th>{{__('adminPanel.betAmount')}}</th>
                                    <th>{{__('adminPanel.rollingAmount')}}</th>
                                    <th>{{__('adminPanel.payout')}}</th>
                                    <th>{{__('adminPanel.betResult')}}</th>
                                    <th>{{__('adminPanel.createdTime')}}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Data table plugin-->
<style>
    td.details-control {
        background: url('/images/open.png') no-repeat center center;
        cursor: pointer;
    }
    tr.shown td.details-control {
        background: url('/images/close.png') no-repeat center center;
    }

</style>

<script type="text/javascript" src="{{ asset('adminPanel/js/Base64.js')}}"></script>
<script type="text/javascript" src="{{ asset('adminPanel/js/plugins/dataTables.bootstrap.min.js')}}"></script>
<script type="text/javascript" src="https://twitter.github.io/typeahead.js/js/handlebars.js"></script>
<script id="details-template" type="text/x-handlebars-template">
    <table class="table table-striped table-bordered" id="sampleTwoTable">
        <tr><td>Game ID:</td><td>@{{gameUUID}}</td></tr>
        <tr><td>Stock ID:</td><td>@{{stockID}}</td></tr>
        <tr><td>Stock Name:</td><td>@{{stockName}}</td></tr>
        <tr><td>Total Number Of Users:</td><td>@{{totalUsers}}</td></tr>
        <tr><td>Total Number Of Bets Placed:</td><td>@{{totalBets}}</td></tr>
        <tr><td>Total Amount Of Bets Placed:</td><td>@{{totalBetAmount}}</td></tr>
        <tr><td>Total Amount Of Profit Earned:</td><td>@{{totalProfitEarned}}</td></tr>
        <tr><td>Game Start Time:</td><td>@{{gameStartDate}} @{{gameStartTime}}</td></tr>
        <tr><td>Game End Time:</td><td>@{{gameEndDate}} @{{gameEndTime}}</td></tr>
        <tr><td>Game End Result Value:</td><td>@{{endResultValue}}</td></tr>
    </table>
</script>
<script type="text/javascript">
    $(document).ready(function(){
        var template = Handlebars.compile($("#details-template").html());
        var table = $('#sampleTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("betHistory") }}',
            language: {
                search: "_INPUT_",
                searchPlaceholder: "{{__('adminPanel.search')}}",
            },
            initComplete: function () {
                var columns = this.api().init().columns;

                this.api().columns().every( function (index) {
                    var column = this;
                    if(column.index() == 9){
                        var select = $('<select><option value="">All</option></select>').appendTo( $(column.footer()).empty() ).on( 'change', function () {
                            var val = $.fn.dataTable.util.escapeRegex(
                                $(this).val()
                            );
                            column.search( val ? '^'+val+'$' : '', true, false ).draw();
                        });
                        column.data().unique().sort().each( function (text) {
                            var types = ['lose','win'];
                            var index = types.indexOf(text);
                            select.append( '<option value="'+index+'">'+text+'</option>' )
                        } );
                    }else{
                        var input = document.createElement("input");
                        if (columns[index].searchable != false) {
                            $(input)
                                .appendTo($(column.footer()).empty())
                                .on("change", function () {
                                    column
                                        .search($(this).val(), false, false, true)
                                        .draw();
                                });
                        }
                    }
                } );
            },
            columns:[
                {
                    className: 'details-control',
                    orderable: false,
                    searchable: false,
                    data: null,
                    defaultContent: ''
                },
				{
                    title: "@lang('adminPanel.userID')",
                    data: "userUUID",
                    name: "user.UUID",
                    render: function(value){
                        var url = 'userProfile/'+btoa(value)+'/get';
                        return "<a href='"+url+"'>"+value+"</a>";
                    }
                },
				{ title: "@lang('adminPanel.betId')", data: "betUUID", name: "betting.UUID" },
				{ title: "@lang('adminPanel.portalProviderID')", data: "portalProviderUUID", name: "portalProvider.UUID" },
				{ title: "@lang('adminPanel.portalProviderName')", data: "portalProviderName", name: "portalProvider.name" },
				{ title: "@lang('adminPanel.ruleName')", data: "ruleName", name: "rule.name" },
				{ title: "@lang('adminPanel.betAmount')", data: "betAmount", name: "betting.betAmount" },
				{ title: "@lang('adminPanel.rollingAmount')", data: "rollingAmount", name: "betting.rollingAmount" },
				{ title: "@lang('adminPanel.payout')", data: "payout", name: "betting.payout" },
				{
                    title: "@lang('adminPanel.betResult')",
                    data: "betResult",
                    render: function(data){
						var statusClass = (data=='win')?"badge-success":"badge-danger";
						return "<span class='badge "+statusClass+"'>"+data+"</span>";
                    }
                },
				{ title: "@lang('adminPanel.createdTime')", data: "betTimeStamp", name: "betting.createdDate" }
            ],
            order: [[1, 'asc']]
        });
        // $('.dataTables_filter').prepend('<label>').children().first().append('@lang('adminPanel.searchByFieldName'):<select class="form-control form-control-sm selectable"><option>@lang('adminPanel.all')</option><option value="0">@lang('adminPanel.userID')</option><option value="2">@lang('adminPanel.portalProviderID')</option><option value="3">@lang('adminPanel.portalProviderName')</option><option value="4">@lang('adminPanel.ruleName')</option><option value="7">@lang('adminPanel.payout')</option><option value="8">@lang('adminPanel.betResult')</option></select>').dataTableFilter(table);
        $('.dataTables_filter').append('<br><button class="btn" onClick="window.location.reload();"><i class="fa fa-refresh" aria-hidden="true"></i>@lang('adminPanel.refresh')</button>').dataTableFilter(table);
        $('#sampleTable tbody').on('click', 'td.details-control', function () {
            var tr = $(this).closest('tr');
            var row = table.row( tr );
            if ( row.child.isShown() ) {
                row.child.hide();
                tr.removeClass('shown');
            } else {
                $.get('betHistory/getSingleGameData/'+row.data().gameUUID,function(response){
                    row.child( template(response) ).show();
                    tr.addClass('shown');
                })
            }
        });
    });
</script>
<!-- Bet History content end -->
@endsection
