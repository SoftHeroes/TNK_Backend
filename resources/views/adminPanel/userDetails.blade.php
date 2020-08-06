@extends('adminPanel.layout.app') @section('content')
<!-- User Details content start -->
<main class="app-content">
	<div class="app-title">
		<div>
			<h1><i class="app-menu__icon fa fa-th-list"></i>{{__('adminPanel.userDetails')}}</h1>
			<p>{{__('adminPanel.dashboardDesc')}}</p>
		</div>
		<ul class="app-breadcrumb breadcrumb side">
			<li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
			<li class="breadcrumb-item active"><a href="{!!route('vUserDetails')!!}">{{__('adminPanel.userDetails')}}</a></li>
		</ul>
	</div>
	@php $value = session(str_replace(".","_",request()->ip()).'ECGames'); @endphp
	<div class="row">
		<div class="col-md-12">
			<div class="tile">
				<div class="tile-body">
					<div class="table-responsive">
						{{-- Table Code Start --}}
						<table class="table table-hover table-bordered" id="sampleTable">
							<thead>
								<tr>
									<th>{{__('adminPanel.userID')}}</th>
									<th>{{__('adminPanel.portalProviderUserID')}}</th>
									<th>{{__('adminPanel.portalProviderID')}}</th>
									<th>{{__('adminPanel.portalProviderName')}}</th>
									<th>{{__('adminPanel.firstName')}}</th>
									<th>{{__('adminPanel.lastName')}}</th>
									<th>{{__('adminPanel.gender')}}</th>
									<th>{{__('adminPanel.emails')}}</th>
									<th>{{__('adminPanel.country')}}</th>
									<th>{{__('adminPanel.balance')}}</th>
									<th>{{__('adminPanel.isLoggedIn')}}</th>
									<th>{{__('adminPanel.lastIP')}}</th>
									<th>{{__('adminPanel.userOnlineMin')}}</th>
								</tr>
							</thead>
							<tbody></tbody>
							<tfoot>
								<tr>
									<th>{{__('adminPanel.userID')}}</th>
									<th>{{__('adminPanel.portalProviderUserID')}}</th>
									<th>{{__('adminPanel.portalProviderID')}}</th>
									<th>{{__('adminPanel.portalProviderName')}}</th>
									<th>{{__('adminPanel.firstName')}}</th>
									<th>{{__('adminPanel.lastName')}}</th>
									<th>{{__('adminPanel.gender')}}</th>
									<th>{{__('adminPanel.emails')}}</th>
									<th>{{__('adminPanel.country')}}</th>
									<th>{{__('adminPanel.balance')}}</th>
									<th>{{__('adminPanel.isLoggedIn')}}</th>
									<th>{{__('adminPanel.lastIP')}}</th>
									<th>{{__('adminPanel.userOnlineMin')}}</th>
								</tr>
							</tfoot>
						</table>
						{{-- Table Code END --}}
					</div>
				</div>
			</div>
		</div>
	</div>
</main>
<style>
	em {
		color: red;
	}
</style>
<!-- Page specific javascripts-->
<!-- Data table plugin-->
<script type="text/javascript" src="{{ asset('adminPanel/js/Base64.js')}}"></script>
<script type="text/javascript" src="{{ asset('adminPanel/js/plugins/dataTables.bootstrap.min.js')}}"></script>
<script type="text/javascript">
	$(document).ready(function(){
		var table = $('#sampleTable').DataTable({
			responsive: true,
			processing: true,
			serverSide: true,
			dom: "<'row'<'col-sm-5'l><'col-sm-7'f>>"+"<'row'<'col-sm-12'tr>>"+"<'row'<'col-sm-5'i><'col-sm-7'p>>",
			language:{
				search: "_INPUT_",
				searchPlaceholder: "{{__('adminPanel.search')}}"
			},
			ajax: '{{ route("vUserDetails") }}',
			columns:[
				{
					title: "@lang('adminPanel.userID')",
					name: "userUUID.value",
					data: "userUUID",
					render: function(e){
						return "<a href="+e.url+">"+e.value+"</a>";
					}
				},
				{ title: "@lang('adminPanel.portalProviderUserID')", data: "portalProviderUserID" },
				{ title: "@lang('adminPanel.portalProviderID')", data: "portalProviderUUID" },
				{ title: "@lang('adminPanel.portalProviderName')", data: "portalProviderName" },
				{ title: "@lang('adminPanel.firstName')", data: "firstName" },
				{ title: "@lang('adminPanel.lastName')", data: "lastName" },
				{ title: "@lang('adminPanel.gender')", data: "gender" },
				{ title: "@lang('adminPanel.emails')", data: "userEmail" },
				{ title: "@lang('adminPanel.country')", data: "country" },
				{ title: "@lang('adminPanel.balance')", data: "userBalance" },
				{
					title: "@lang('adminPanel.isLoggedIn')",
					data: "userLoggedInStatus",
					render: function(data){
						var status = data == 'true'? "online":"offline";
						var statusClass = data == 'true' ? "badge-success":"badge-danger";
						return "<span class='badge "+statusClass+"'>"+status+"</span>";
					}
				},
				{ title: "@lang('adminPanel.lastIP')", data: "userLastIP" },
				{ title: "@lang('adminPanel.userOnlineMin')", data: "userOnlineMin" },
			],
			initComplete: function () {
            this.api().columns().every( function (e) {
                var column = this;
                if (e == 10) {
                    var select = $('<select><option value=""></option></select>')
                    .appendTo( $(column.footer()).empty() )
                    .on( 'change', function () {
                        var val = $.fn.dataTable.util.escapeRegex($(this).val());
                        column.search( val, true, false ).draw();
                    } );
                    
                    column.data().unique().sort().each( function () {
                    select.html( '<option value="">All</option><option value="true">online</option><option value="false">offline</option>' )
                } );
                } else if (e == 6) {
                    var select = $('<select><option value=""></option></select>')
                    .appendTo( $(column.footer()).empty() )
                    .on( 'change', function () {
                        var val = $.fn.dataTable.util.escapeRegex($(this).val());
                        column.search( val, true, false ).draw();
                    } );
                    
                    column.data().unique().sort().each( function () {
                    select.html( '<option value="">All</option><option value="male">male</option><option value="female">female</option><option value="other">other</option>' )
                } );
                } else {
                    var select = $('<input name="" value="">')
                    .appendTo( $(column.footer()).empty() )
                    .on( 'change', function () {
                        var val = $.fn.dataTable.util.escapeRegex( $(this).val() );
                        column.search( val, true, false ).draw();
                    } );
                    
                    column.data().unique().sort().each( function ( d, j ) {
                    select.append( '<option name="'+d+'" value="'+d+'">' )
                } );
            	 }
            });
        }
		});
		$('.dataTables_filter').append('<br><button class="btn" onClick="window.location.reload();"><i class="fa fa-refresh" aria-hidden="true"></i>@lang('adminPanel.refresh')</button>').dataTableFilter(table);
	});
</script>
<!-- game History content end -->
@endsection