@extends('adminPanel.layout.app')
@section('content')
<!-- Game History content start -->
<main class="app-content">
    <div class="app-title">
        <div>
            <h1><i class="app-menu__icon fa fa-th-list"></i>{{__('adminPanel.gameHistory')}}</h1>
            <p>{{__('adminPanel.dashboardDesc')}}</p>
        </div>
        <ul class="app-breadcrumb breadcrumb side">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item active"><a href="{!!route('vGameHistory')!!}">{{__('adminPanel.gameHistory')}}</a></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="tile">
                <div class="tile-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered" id="sampleTable">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th> {{__('adminPanel.gameID')}} </th>
                                    <th> {{__('adminPanel.portalProviderID')}} </th>
                                    <th> {{__('adminPanel.portalProviderName')}} </th>
                                    <th> {{__('adminPanel.stockID')}} </th>
                                    <th> {{__('adminPanel.stockName')}} </th>
                                    <th> {{__('adminPanel.totalUsers')}} </th>
                                    <th> {{__('adminPanel.totalBets')}} </th>
                                    <th> {{__('adminPanel.totalBetAmount')}} </th>
                                    <th> {{__('adminPanel.totalRollingAmount')}} </th>
                                    <th> {{__('adminPanel.totalProfitEarned')}} </th>
                                    <th> {{__('adminPanel.endDateTime')}} </th>
                                    <th> {{__('adminPanel.endStockValue')}} </th>
                                    <th> {{__('adminPanel.gameStatus')}} </th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr>
                                    <th></th>
                                    <th class="filter-gameID" >{{__('adminPanel.gameID')}}</th>
                                    <th class="filter-portalProviderID" >{{__('adminPanel.portalProviderID')}}</th>
                                    <th class="filter-portalProviderName" >{{__('adminPanel.portalProviderName')}}</th>
                                    <th class="filter-stockID" >{{__('adminPanel.stockID')}}</th>
                                    <th class="filter-stockName" >{{__('adminPanel.stockName')}}</th>
                                    <th class="filter-totalUsers" ></th>
                                    <th class="filter-totalBets" ></th>
                                    <th class="filter-totalBetAmount" ></th>
                                    <th class="filter-totalRollingAmount" ></th>
                                    <th class="filter-totalProfitEarned" ></th>
                                    <th class="filter-endDateTime" >{{__('adminPanel.endDateTime')}}</th>
                                    <th class="filter-endStockValue" >{{__('adminPanel.endStockValue')}}</th>
                                    <th class="filter-gameStatus" >{{__('adminPanel.gameStatus')}}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<!-- Page specific javascript-->
<!-- Data table plugin-->
<script type="text/javascript" src="{{ asset('adminPanel/js/plugins/dataTables.bootstrap.min.js')}}"></script>
<script type="text/javascript" src="https://twitter.github.io/typeahead.js/js/handlebars.js"></script>
<script id="details-template" type="text/x-handlebars-template">
    <table class="table table table-striped table-bordered details-table" id="child-@{{gameUUID}}">
        <thead>
            <tr>
                <th>{{__('adminPanel.userID')}}</th>
                <th>{{__('adminPanel.betId')}}</th>
                <th>{{__('adminPanel.betAmount')}}</th>
                <th>{{__('adminPanel.payout')}}</th>
                <th>{{__('adminPanel.rollingAmount')}}</th>
                <th>{{__('adminPanel.endDateTime')}}</th>
                <th>{{__('adminPanel.betResult')}}</th>
            </tr>
        </thead>
    </table>
</script>
<script type="text/javascript">
    function initTable(tableId, data) {
        $('#' + tableId).DataTable({
            processing: true,
            serverSide: true,
            ajax: 'gameHistory/getGameDetail/' + data.gameUUID,
            language:{
                search: "_INPUT_",
                searchPlaceholder: "{{__('adminPanel.search')}}"
            },
            columns: [
                { data: 'userUUID', name: 'user.UUID' },
                { data: 'bettingUUID', name: 'betting.UUID' },
                { data: 'betAmount', name: 'betting.betAmount' },
                { data: 'payoutAmount', name: 'betting.payout' },
                { data: 'rollingAmount', name: 'betting.rollingAmount' },
                {
                    data: 'gameEndDate',
                    name: 'game.endDate',
                    render: function(data,type,row){
                        return row.gameEndDate + ' ' + row.gameEndTime;
                    }
                },
                {
                    data: 'betResult',
                    name: 'betResult',
                    render: function(data){
						var statusClass = (data=='win')?"badge-success":"badge-danger";
						return "<span class='badge "+statusClass+"'>"+data+"</span>";
                    }
                }
            ]
        })
    }
    $(document).ready(function(){

        var param = new URLSearchParams(window.location.search);

        let stockID,portalProviderID;
        if(param.has('stockID')){
            stockID = param.get('stockID');
        }
        if(param.has('portalProviderID')){
            portalProviderID = param.get('portalProviderID');
        }

        var template = Handlebars.compile($("#details-template").html());
        // Sample table - DataTable invoking.
        var table = $('#sampleTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("gameHistory") }}',
            language:{
                search: "_INPUT_",
                searchPlaceholder: "{{__('adminPanel.search')}}"
            },
            "searchCols":[  // Setting init data for Filter 
                null,
                null,
                { "search": portalProviderID},
                null,
                { "search": stockID},
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null
            ],
            initComplete: function () {
                var columns = this.api().init().columns;

                this.api().columns().every( function (index) {
                    var column = this;
                    if(column.index() == 13){
                        var select = $('<select><option value="">All</option></select>').appendTo( $(column.footer()).empty() ).on( 'change', function () {
                            var val = $.fn.dataTable.util.escapeRegex(
                                $(this).val()
                            );
                            column.search( val ? '^'+val+'$' : '', true, false ).draw();
                        });
                        column.data().unique().sort().each( function (text) {
                            var types = ['Pending' , 'Open' , 'Close' , 'Complete', 'Error' , 'Deleted'];
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

                // Setting Data in Filter fields : START 
                if(typeof stockID !== "undefined"){
                    table.column(4).search(stockID).draw();
                    $('.filter-stockID input').val(stockID);
                }

                if(typeof portalProviderID !== "undefined"){
                    table.column(2).search(portalProviderID).draw();
                    $('.filter-portalProviderID input').val(portalProviderID);
                }
                // Setting Data in Filter fields : END
                
            },
            columns:[
                {
                    className: 'details-control',
                    orderable: false,
                    searchable: false,
                    data: null,
                    defaultContent: ''
                },
				{ title: "{{__('adminPanel.gameID')}}", data: "gameUUID", name: "game.UUID"},
                { title: "{{__('adminPanel.portalProviderID')}}", data: "portalProviderUUID", name: "portalProvider.UUID"},
                { title: "{{__('adminPanel.portalProviderName')}}", data: "portalProviderName", name: "portalProvider.name"},
                { title: "{{__('adminPanel.stockID')}}", data: "stockUUID", name: "stock.UUID"},
                { title: "{{__('adminPanel.stockName')}}", data: "stockName", name: "stock.name"},
                { title: "{{__('adminPanel.totalUsers')}}", data: "totalUsers", searchable: false},
                { title: "{{__('adminPanel.totalBets')}}", data: "totalBets", searchable: false},
                { title: "{{__('adminPanel.totalBetAmount')}}", data: "totalBetAmount", searchable: false},
                { title: "{{__('adminPanel.totalRollingAmount')}}", data: "totalRollingAmount", searchable: false},
                { title: "{{__('adminPanel.totalProfitEarned')}}", data: "totalProfitEarned", searchable: false},
                {
                    title: "{{__('adminPanel.endDateTime')}}",
                    data: "gameEndDate",
                    name: "game.endDate",
                    render: function(data,type,row){
                        return row.gameEndDate + ' ' + row.gameEndTime;
                    }
                },
                { title: "{{__('adminPanel.endStockValue')}}", data: "endStockValue", name: "game.endStockValue"},
                {
                    title: "{{__('adminPanel.gameStatus')}}",
                    data: "gameStatus",
                    name: "gameStatus",
                    render: function(data){
                        var statusClass = null;
                        if(data == 'Complete'){
                            statusClass = 'badge-success';
                        }else if(data == 'Open'){
                            statusClass = 'badge-primary';
                        }else if(data == 'Close'){
                            statusClass = 'badge-secondary';
                        }else if(data == 'Error'){
                            statusClass = 'badge-danger';
                        }
                        return '<span class="badge '+statusClass+'">'+data+'</span>';
                    }
                }
            ],
            order: [[1, 'asc']]
        });

        // Add event listener for opening and closing details
        $('#sampleTable tbody').on('click', 'td.details-control', function () {
            var tr = $(this).closest('tr');
            var row = table.row(tr);
            var tableId = 'child-' + row.data().gameUUID;
            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
            } else {
                row.child(template(row.data())).show();
                initTable(tableId, row.data());
                tr.addClass('shown');
                tr.next().find('td').addClass('no-padding');
            }
        });
    });
</script>
<!-- game History content end -->
@endsection
