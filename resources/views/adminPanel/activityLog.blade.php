@extends('adminPanel.layout.app') @section('content')
<!-- Bet History content start -->
<main class="app-content">
    <div class="app-title">
        <div>
            <h1><i class="app-menu__icon fa fa-th-list"></i> {{__('adminPanel.activityLog')}}</h1>
            <p>{{__('adminPanel.dashboardDesc')}}</p>
        </div>
        <ul class="app-breadcrumb breadcrumb side">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item active">{{__('adminPanel.log')}}</li>
            <li class="breadcrumb-item active"><a href="{!!route('vActivityLog')!!}">{{__('adminPanel.activityLog')}}</a></li>
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
                                    <th>{{__('adminPanel.portalProviderID')}}</th>
                                    <th>{{__('adminPanel.portalProviderName')}}</th>
                                    <th>{{__('adminPanel.userID')}}</th>
                                    <th>{{__('adminPanel.username')}}</th>
                                    <th>{{__('adminPanel.service')}}</th>
                                    <th>{{__('adminPanel.method')}}</th>
                                    <th>{{__('adminPanel.responseCode')}}</th>
                                    <th>{{__('adminPanel.responseMessage')}}</th>
                                    <th>{{__('adminPanel.apiStatus')}}</th>
                                    <th>{{__('adminPanel.requestTime')}}</th>
                                    <th>{{__('adminPanel.ipAddress')}}</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr>
                                    <td>{{__('adminPanel.portalProviderID')}}</td>
                                    <td>{{__('adminPanel.portalProviderName')}}</td>
                                    <td>{{__('adminPanel.userID')}}</td>
                                    <td>{{__('adminPanel.username')}}</td>
                                    <td>{{__('adminPanel.service')}}</td>
                                    <td>{{__('adminPanel.method')}}</td>
                                    <td>{{__('adminPanel.responseCode')}}</td>
                                    <td>{{__('adminPanel.responseMessage')}}</td>
                                    <td>{{__('adminPanel.apiStatus')}}</td>
                                    <td>{{__('adminPanel.requestTime')}}</td>
                                    <td>{{__('adminPanel.ipAddress')}}</td>
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
        var table = $('#sampleTable').DataTable({
            responsive: true,
            dom: "<'row'<'col-sm-5'l><'col-sm-7'f>>"+"<'row'<'col-sm-12'tr>>"+"<'row'<'col-sm-5'i><'col-sm-7'p>>",
            language:{
                search: "_INPUT_",
                searchPlaceholder: "{{__('adminPanel.search')}}"
            },
            processing: true,
			serverSide: true,
            ajax: '{{route("vActivityLog")}}',
            initComplete: function () {
                var columns = this.api().init().columns;

                this.api().columns().every( function (index) {
                    var column = this;
                    if(column.index() == 8){
                        var select = $('<select><option value="">All</option></select>').appendTo( $(column.footer()).empty() ).on( 'change', function () {
                            var val = $.fn.dataTable.util.escapeRegex(
                                $(this).val()
                            );
                            column.search( val ? '^'+val+'$' : '', true, false ).draw();
                        });
                        select.append( '<option value="0">Success</option>' )
                        select.append( '<option value="1">Error</option>' )
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
                    title: "@lang('adminPanel.portalProviderID')",
                    data: "portalProviderID",
                    name: "portalProvider.UUID"
                },
				{ title: "@lang('adminPanel.portalProviderName')", data: "portalProviderName", name: "portalProvider.name" },
				{ title: "@lang('adminPanel.userID')", data: "userID", name: "user.UUID " },
				{ title: "@lang('adminPanel.username')", data: "username", name:"admin.username"},
				{ title: "@lang('adminPanel.service')", data: "service", name: "apiActivityLog.service" },
				{ title: "@lang('adminPanel.method')", data: "method", name: "apiActivityLog.method" },
				{ title: "@lang('adminPanel.responseCode')", data: "responseCode", name: "apiActivityLog.responseCode" },
				{ title: "@lang('adminPanel.responseMessage')", data: "responseMessage", name: "apiActivityLog.responseMessage" },
				{
                    title: "@lang('adminPanel.apiStatus')",
                    data: "errorFound",
                    render: function(data){
						var statusClass = (!data) ? "badge-success":"badge-danger";
						var statusErrorFound = (!data)? "@lang('adminPanel.success')":"@lang('adminPanel.errorFound')";
						return "<span class='badge "+statusClass+"'>"+statusErrorFound+"</span>";
                    },
                },
				{ title: "@lang('adminPanel.requestTime')", data: "requestTime", name: "apiActivityLog.requestTime" },
				{ title: "@lang('adminPanel.ipAddress')", data: "ipAddress", name: "apiActivityLog.ipAddress" }
            ]
        });
    });
</script>

<!-- Bet History content end -->
@endsection
