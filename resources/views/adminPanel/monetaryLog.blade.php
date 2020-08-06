@extends('adminPanel.layout.app') @section('content')
<!-- Bet History content start -->
<main class="app-content">
    <div class="app-title">
        <div>
            <h1><i class="app-menu__icon fa fa-th-list"></i> {{__('adminPanel.monetaryLog')}}</h1>
            <p>{{__('adminPanel.dashboardDesc')}}</p>
        </div>
        <ul class="app-breadcrumb breadcrumb side">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item active">{{__('adminPanel.log')}}</li>
            <li class="breadcrumb-item active"><a href="{!!route('vMonetaryLog')!!}">{{__('adminPanel.monetaryLog')}}</a></li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="tile">
                <div class="tile-body">
                    <div class="table-responsive">

                        <table class="table table-hover table-bordered" id="tableMonetaryLog">
                            <thead>
                                <tr>
                                    <th>{{__('adminPanel.portalProviderID')}}</th>
                                    <th>{{__('adminPanel.portalProviderName')}}</th>
                                    <th>{{__('adminPanel.userID')}}</th>
                                    <th>{{__('adminPanel.username')}}</th>
                                    <th>{{__('adminPanel.previousBalance')}}</th>
                                    <th>{{__('adminPanel.newBalance')}}</th>
                                    <th>{{__('adminPanel.amount')}}</th>
                                    <th>{{__('adminPanel.balanceType')}}</th>
                                    <th>{{__('adminPanel.operation')}}</th>
                                    <th>{{__('adminPanel.UUID')}}</th>
                                    <th>{{__('adminPanel.serviceName')}}</th>
                                    <th>{{__('adminPanel.datetime')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td>{{__('adminPanel.portalProviderID')}}</td>
                                    <td>{{__('adminPanel.portalProviderName')}}</td>
                                    <td>{{__('adminPanel.userID')}}</td>
                                    <td>{{__('adminPanel.username')}}</td>
                                    <td>{{__('adminPanel.previousBalance')}}</td>
                                    <td>{{__('adminPanel.newBalance')}}</td>
                                    <td>{{__('adminPanel.amount')}}</td>
                                    <td>{{__('adminPanel.balanceType')}}</td>
                                    <td>{{__('adminPanel.operation')}}</td>
                                    <td>{{__('adminPanel.UUID')}}</td>
                                    <td>{{__('adminPanel.serviceName')}}</td>
                                    <td>{{__('adminPanel.datetime')}}</td>
                                </tr>
                            </tfoot>
                        </table>
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
        var table = $('#tableMonetaryLog').DataTable({
            responsive: true,
            dom: "<'row'<'col-sm-5'l><'col-sm-7'f>>"+"<'row'<'col-sm-12'tr>>"+"<'row'<'col-sm-5'i><'col-sm-7'p>>",
            language:{
                search: "_INPUT_",
                searchPlaceholder: "{{__('adminPanel.search')}}"
            },
            processing: true,
			serverSide: true,
            ajax: '{{route("vMonetaryLog")}}',
            columns:[
				{ title: "@lang('adminPanel.portalProviderID')",data: "portalProviderID",name: "portalProvider.UUID"},
				{ title: "@lang('adminPanel.portalProviderName')", data: "portalProviderName", name: "portalProvider.name" },
				{ title: "@lang('adminPanel.userID')", data: "userID", name: "user.UUID " },
				{ title: "@lang('adminPanel.username')", data: "username", name: "admin.username"},
				{ title: "@lang('adminPanel.previousBalance')", data: "previousBalance", name: "poolLog.previousBalance" },
				{ title: "@lang('adminPanel.newBalance')", data: "newBalance", name: "poolLog.newBalance" },
				{ title: "@lang('adminPanel.amount')", data: "amount", name: "poolLog.amount" },
				{ title: "@lang('adminPanel.balanceType')", data: "balanceType", name: "poolLog.balanceType" },
				{
                    title: "@lang('adminPanel.operation')",
                    data: "operation",
                    render: function(data){
						var statusClass = data === 'Credit' ? "badge-success": data === 'Debit' ? "badge-danger" : data === 'recharge' ? 'badge-warning':'badge-danger';
						return "<span class='badge "+statusClass+"'>"+data+"</span>";
                    },
                    name: "poolLog.operation"
                },
				{ title: "@lang('adminPanel.UUID')", data: "UUID", name: "poolLog.UUID" },
                { title: "@lang('adminPanel.serviceName')", data: "serviceName", name: "poolLog.serviceName" },
                { title: "@lang('adminPanel.datetime')", data: "createdAt", name: "poolLog.createdAt" }
                
            ],
            order: [[1, 'asc']],
            initComplete: function () {
                var columns = this.api().init().columns;

                this.api().columns().every( function (index) {
                    var column = this;

                    if(column.index() == 8){
                        var input = document.createElement("input");
                        if (columns[index].searchable != false) {
                            $(input)
                                .appendTo($(column.footer()).empty())
                                .on("change", function () {
                                    var tmp = $(this).val().toLowerCase();
                                    var recharge = "recharge";
                                    var debit = "debit";
                                    var credit = "credit";

                                    if(recharge.indexOf(tmp) != -1) {
                                       var searchVal = 2;
                                       column
                                        .search(searchVal, false, false, true)
                                        .draw();
                                    }else if (credit.indexOf(tmp) != -1) {
                                        var searchVal = 0;
                                        column
                                            .search(searchVal, false, false, true)
                                            .draw();
                                    } else if (debit.indexOf(tmp) != -1) {
                                        var searchVal = 1;
                                        column
                                            .search(searchVal, false, false, true)
                                            .draw();
                                    } else {
                                        column
                                            .search($(this).val(), false, false, true)
                                            .draw();
                                    }
                                });
                        }
                    } 
                    else{
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
                });
            }
        });
    });
</script>
<!-- Bet History content end -->
@endsection
