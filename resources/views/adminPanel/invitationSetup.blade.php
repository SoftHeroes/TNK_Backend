@extends('adminPanel.layout.app') @section('content')
<main class="app-content">
	<div class="app-title">
		<div>
			<h1><i class="app-menu__icon fa fa-cog"></i>{{__('adminPanel.invitationSetup')}}</h1>
			<p>{{__('adminPanel.dashboardDesc')}}</p>
		</div>
		<ul class="app-breadcrumb breadcrumb side">
			<li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
			<li class="breadcrumb-item active"><a href="{!!route('vInvitationSetup')!!}">{{__('adminPanel.invitationSetup')}}</a></li>
		</ul>
	</div>
	<!-- Modal -->
	<div class="modal fade bd-12-modal-lg" id="createInvitationSetupModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLongTitle">{{__('adminPanel.newInvitationSetup')}}</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body">
					<span class="text-danger">* {{__('adminPanel.requiredIndication')}}</span><br><br>
					<form action="{{route('vCreateInvitationSetup')}}" class="createInvitationSetup-form" method="POST">
						{{csrf_field()}}
						<div class="row">
							<div class="col-lg-12">

								<div class="form-group">
									<label class="control-label">{{__('adminPanel.name')}} <span class="text-danger">*</span></label>
									<div class="form-group">
										<input class="form-control" name="name" type="text" placeholder="{{__('adminPanel.name')}}">
									</div>
                                </div>

                                <div class="form-group">
									<label class="control-label">{{__('adminPanel.maximumRequestInDay')}}</label>
									<div class="form-group">
										<input class="form-control" name="maximumRequestInDay" type="number" placeholder="{{__('adminPanel.maximumRequestInDay')}}">
									</div>
                                </div>

                                <div class="form-group">
									<label class="control-label">{{__('adminPanel.requestMin')}}</label>
									<div class="form-group">
										<input class="form-control" name="requestMin" type="number" placeholder="{{__('adminPanel.requestMin')}}">
									</div>
                                </div>

                                <div class="form-group">
									<label class="control-label">{{__('adminPanel.maximumRequestInMin')}}</label>
									<div class="form-group">
										<input class="form-control" name="maximumRequestInMin" type="number" placeholder="{{__('adminPanel.maximumRequestInMin')}}">
									</div>
                                </div>

							</div>
						</div>
						<div class="col-lg-12">
							<button class="btn btn-primary" type="submit" id="btnSubmit">{{__('adminPanel.create')}}</button>
							<button class="btn btn-danger" type="button" data-dismiss="modal" aria-label="Close">{{__('adminPanel.cancel')}}</button>
						</div>
					</form>
				</div>
			</div>
		</div>
    </div>

	<div class="row">
		<div class="col-md-12">
			<div class="tile">
				<div class="tile-body">
					<div class="table-responsive">
                        @if($isAllowAll == 'true' || (($isAllowAll == 'false') && (($accessibility == 1) || ($accessibility == 2))))
                            <div class="form-group col-md-4">
                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createInvitationSetupModal">
                                {{__('adminPanel.newInvitationSetup')}} <i class="fa fa-plus"></i>
                                </button>
                            </div>
                        @endif
						<table class="table table-hover table-bordered" id="sampleTable">
							<thead>
								<tr>
									<th>{{__('adminPanel.name')}}</th>
									<th>{{__('adminPanel.maximumRequestInDay')}}</th>
									<th>{{__('adminPanel.requestMin')}}</th>
									<th>{{__('adminPanel.maximumRequestInMin')}}</th>
                                    <th>{{__('adminPanel.createdAt')}}</th>
                                    <th>{{__('adminPanel.updatedAt')}}</th>
                                    <th>{{__('adminPanel.deletedAt')}}</th>
                                    <th>{{ __('adminPanel.action') }}</th>
								</tr>
							</thead>
							<tbody>
								@isset($invitationSetupData) @foreach ($invitationSetupData as $invitationSetup)
								<tr @php if ($invitationSetup->deletedAt != Null) { echo "style=background-color:#f37575";} @endphp>
									<td>{{ $invitationSetup->name }}</td>
									<td>{{ $invitationSetup->maximumRequestInDay }}</td>
									<td>{{ $invitationSetup->requestMin }}</td>
									<td>{{ $invitationSetup->maximumRequestInMin }}</td>
									<td>{{ $invitationSetup->createdAt }}</td>
                                    <td>{{ $invitationSetup->updatedAt }}</td>
                                    <td>@if($invitationSetup->deletedAt != Null)
                                            {{$invitationSetup->deletedAt}}
                                        @else
                                        {{__('adminPanel.null')}}
                                        @endif</td>
									<td>
                                        <div class="d-flex">
                                            @if($invitationSetup->deletedAt == Null)
                                                @if($isAllowAll == 'true' || (($isAllowAll == 'false') && ($accessibility == 2)))
                                                    <form action="{{ route('vDeleteInvitationSetup') }}" method="post">
                                                        {{csrf_field()}}
                                                        <input hidden name="invitationSetupID" value="{{$invitationSetup->PID}}">
                                                        <button class="btn btn-danger btn-sm" onclick="return confirm('@lang('adminPanel.msgDelete')')" type="submit"> <i class="fa fa-trash"></i></button>
                                                    </form>
                                                @endif
                                                @if($isAllowAll == 'true' || (($isAllowAll == 'false') && (($accessibility == 1) || ($accessibility == 2))))
                                                    <button type="button" class="btn btn-primary btn-sm ml-1" data-toggle="modal" data-target="#editInvitationSetupModal{{$invitationSetup->PID}}"><i class="fa fa-edit"></i></button>
                                                @endif
                                            @else
                                            <form action="{{ route('vRestoreInvitationSetup') }}" method="post">
                                                {{csrf_field()}}
                                                <input hidden name="invitationSetupID" value="{{$invitationSetup->PID}}">
                                                <button class="btn btn-success btn-sm" onclick="return confirm('Are you sure to restore?')" type="submit"> <i class="fa fa-refresh">{{__('adminPanel.restore')}}</i></button>
                                            </form>
                                            @endif
                                        </div>

                                        {{-- modal editInvitationSetupModal --}}

                                        <div class="modal fade bd-12-modal-lg" id="editInvitationSetupModal{{$invitationSetup->PID}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                            <div class="modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="exampleModalLongTitle">{{__('adminPanel.editInvitationSetup')}}</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <span class="text-danger">* {{__('adminPanel.requiredIndication')}}</span><br><br>
                                                        <form action="{{route('vUpdateInvitationSetup')}}" class="editInvitationSetupModal-form{{$invitationSetup->PID}}" method="POST" >
                                                            {{csrf_field()}}
                                                            <input hidden name="invitationSetupID" value="{{$invitationSetup->PID}}">
                                                            <div class="row">
                                                                <div class="col-lg-12">

                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.name')}} <span class="text-danger">*</span></label>
                                                                        <div class="form-group">
                                                                            <input class="form-control" name="name" type="text" placeholder="{{__('adminPanel.name')}}" value="{{ $invitationSetup->name }}">
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.maximumRequestInDay')}}</label>
                                                                        <div class="form-group">
                                                                            <input class="form-control" name="maximumRequestInDay" type="number" placeholder="{{__('adminPanel.maximumRequestInDay')}}" value="{{ $invitationSetup->maximumRequestInDay }}">
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.requestMin')}}</label>
                                                                        <div class="form-group">
                                                                            <input class="form-control" name="requestMin" type="number" placeholder="{{__('adminPanel.requestMin')}}" value="{{ $invitationSetup->requestMin }}">
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.maximumRequestInMin')}}</label>
                                                                        <div class="form-group">
                                                                            <input class="form-control" name="maximumRequestInMin" type="number" placeholder="{{__('adminPanel.maximumRequestInMin')}}" value="{{ $invitationSetup->maximumRequestInMin }}">
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                            </div>
                                                            <div class="col-lg-12">
                                                                <button class="btn btn-primary" type="submit" id="btnSubmit">{{__('adminPanel.save')}}</button>
                                                                <button class="btn btn-danger" type="button" data-dismiss="modal" aria-label="Close">{{__('adminPanel.cancel')}}</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <script>
											$(document).ready(function() {
											    var invitationSetupID = {{$invitationSetup->PID}};
                                                $(".editInvitationSetupModal-form"+invitationSetupID).validate({
                                                    rules: {
                                                        name: {
                                                            required: true
                                                        }
                                                    },
                                                    messages: {
                                                        name: {
                                                            required: "Please Enter Name"
                                                        }
                                                    },

                                                    errorPlacement: function(error, element) {
                                                        error.insertAfter(element.parent());
                                                    }
                                                });
                                            });
										</script>
									</td>
								</tr>
								@endforeach @endisset
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</main>
<!-- Page specific javascripts-->
<!-- Data table plugin-->
<style>
	em {
	    color: red;
	}
</style>
<script type="text/javascript" src="{{ asset('adminPanel/js/plugins/dataTables.bootstrap.min.js')}}"></script>
<script type="text/javascript">
    $(document).ready(function(){
        var table = $('#sampleTable').DataTable({
            responsive: true,
            dom: "<'row'<'col-sm-5'l><'col-sm-7'f>>"+"<'row'<'col-sm-12'tr>>"+"<'row'<'col-sm-5'i><'col-sm-7'p>>",
            language:{
                search: "_INPUT_",
                searchPlaceholder: "{{__('adminPanel.search')}}"
            }
        });
        $('.dataTables_filter').prepend('<label>').children().first().append('@lang('adminPanel.searchByFieldName'):<select class="form-control form-control-sm selectable"><option>@lang('adminPanel.all')</option><option value="0">@lang('adminPanel.name')</option><option value="1">@lang('adminPanel.maximumRequestInDay')</option><option value="2">@lang('adminPanel.requestMin')</option><option value="3">@lang('adminPanel.maximumRequestInMin')</option><option value="4">@lang('adminPanel.createdAt')</option><option value="5">@lang('adminPanel.updatedAt')</option></select>').dataTableFilter(table);
        $('.dataTables_filter').append('<br><button class="btn" onClick="window.location.reload();"><i class="fa fa-refresh" aria-hidden="true"></i>@lang('adminPanel.refresh')</button>').dataTableFilter(table);
        $('.dataTables_filter').append('<input type="checkbox" id="includeDeleted" value="true"> <label>@lang('adminPanel.includeDeleted')</label>').dataTableFilter(table);

        //To get the actual checkbox value from the local storage
        $('#includeDeleted').prop('checked', localStorage.getItem('id') === 'true');

        $('#includeDeleted').click(function(e) {

            // saving the checkbox value in local storage, to get the actual value during page refresh
            localStorage.setItem('id', $(this).prop('checked'));

            var saveSessionUrl = '{{ route("saveIncludeDeletedSession") }}';
            var updateSessionUrl = '{{ route("updateSession") }}';
            var removeSessionUrl = '{{ route("removeIncludeDeletedSession") }}'

            if($(this).is(':checked')) {
                saveIncludeDeletedSession(saveSessionUrl, updateSessionUrl);
            } else {
                removeIncludeDeletedSession(removeSessionUrl, updateSessionUrl);
            }
        });
    });
</script>
<!-- game History content end -->
@endsection
