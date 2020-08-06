@extends('adminPanel.layout.app') @section('content')
<main class="app-content">
	<div class="app-title">
		<div>
			<h1><i class="app-menu__icon fa fa-th-list"></i>{{__('adminPanel.providerConfig')}}</h1>
			<p>{{__('adminPanel.dashboardDesc')}}</p>
		</div>
		<ul class="app-breadcrumb breadcrumb side">
			<li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
			<li class="breadcrumb-item active"><a href="{!!route('vProviderConfig')!!}">{{__('adminPanel.providerConfig')}}</a></li>
		</ul>
	</div>
	<!-- Modal -->
	<div class="modal fade bd-12-modal-lg" id="addProviderConfig" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLongTitle">{{__('adminPanel.providerConfig')}}</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body">
					<span class="text-danger">* {{__('adminPanel.requiredIndication')}}</span><br><br>
					<form action="{{route('vAddProviderConfig')}}" class="AddNewProviderConfig-form" method="POST" enctype="multipart/form-data">
						{{csrf_field()}}
						<div class="row">
							<div class="col-lg-12">
                                <div class="form-group">
									<label class="control-label">{{__('adminPanel.portalProviderName')}} <span class="text-danger">*</span></label>
									<div class="form-group">
										<div class="form-group-select">
											<select class="form-control" name="portalProviderID">
												<option value="">{{__('adminPanel.selectPortalProvider')}}</option>
												@foreach ($portalProviderData as $portalProvider)
												@if ($portalProvider->PID != "1") {
												<option value="{{$portalProvider->PID}}">{{$portalProvider->name}} </option>
												@endif
												@endforeach
											</select>
										</div>
									</div>
                                </div>

								<div class="form-group">
									<label class="control-label">{{__('adminPanel.followBetSetupID')}}</label>
									<div class="form-group">
										<div class="form-group-select">
											<select class="form-control" name="followBetSetupID">
												<option value="">{{__('adminPanel.select')}} {{__('adminPanel.followBetSetupID')}}</option>
												@foreach ($followBetSetupData as $followBetSetup)
												<option value="{{$followBetSetup->PID}}">{{$followBetSetup->PID}}</option>
												@endforeach
											</select>
										</div>
									</div>
                                </div>

								<div class="form-group">
									<label class="control-label">{{__('adminPanel.logoutAPICall')}}</label>
									<div class="form-group">
										<input class="form-control" name="logoutAPICall" type="number" placeholder="{{__('adminPanel.logoutAPICall')}}">
									</div>
								</div>

								<div class="form-group">
									<label class="control-label">{{__('adminPanel.invitationSetup')}}</label>
									<div class="form-group">
										<div class="form-group-select">
											<select class="form-control" name="invitationSetupID">
												<option value="">{{__('adminPanel.select')}} {{__('adminPanel.invitationSetup')}}</option>
												@foreach ($invitationSetupData as $invitationSetup)
												<option value="{{$invitationSetup->PID}}">{{$invitationSetup->name}}</option>
												@endforeach
											</select>
										</div>
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
								<button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addProviderConfig">
								{{__('adminPanel.providerConfig')}} <i class="fa fa-plus"></i>
								</button>
							</div>
						@endif
						<table class="table table-hover table-bordered" id="sampleTable">
							<thead>
								<tr>
									<th>{{__('adminPanel.providerConfigID')}}</th>
									<th>{{__('adminPanel.portalProviderName')}}</th>
									<th>{{__('adminPanel.followBetSetupID')}}</th>
									<th>{{__('adminPanel.logoutAPICall')}}</th>
									<th>{{__('adminPanel.invitationSetupID')}}</th>
                                    <th>{{__('adminPanel.createdAt')}}</th>
                                    <th>{{__('adminPanel.updatedAt')}}</th>
									<th>{{__('adminPanel.deletedAt')}}</th>
                                    <th>{{ __('adminPanel.action') }}</th>
								</tr>
							</thead>
							<tbody>
								@isset($providerConfigData) @foreach ($providerConfigData as $providerConfig)
								<tr @php if ($providerConfig->deletedAt != Null) { echo "style=background-color:#f37575";} @endphp>
									<td>{{ $providerConfig->providerConfigID }}</td>
									<td>{{ $providerConfig->portalProviderName }}</td>
									<td>{{ $providerConfig->followBetSetupID }}</td>
									<td>{{ $providerConfig->logoutAPICall }}</td>
									<td>{{ $providerConfig->invitationSetupName }}</td>
									<td>{{ $providerConfig->createdAt }}</td>
									<td>{{ $providerConfig->updatedAt }}</td>
									<td> @if($providerConfig->deletedAt != Null)
                                            {{$providerConfig->deletedAt}}
                                        @else
                                        {{__('adminPanel.null')}}
                                        @endif</td>
									<td>
                    					<div class="d-flex">
											@if($providerConfig->deletedAt == Null)
												@if($isAllowAll == 'true' || (($isAllowAll == 'false') && ($accessibility == 2)))
													<form action="{{ route('vDeleteProviderConfig') }}" method="post">
														{{csrf_field()}}
														<input hidden name="providerConfigID" value="{{$providerConfig->providerConfigID}}">
														<button class="btn btn-danger btn-sm" onclick="return confirm('@lang('adminPanel.msgDelete')')" type="submit"> <i class="fa fa-trash"></i></button>
													</form>
												@endif
												@if($isAllowAll == 'true' || (($isAllowAll == 'false') && (($accessibility == 1) || ($accessibility == 2))))
													<button type="button" class="btn btn-primary btn-sm ml-1" data-toggle="modal" data-target="#editProviderConfigModal{{$providerConfig->providerConfigID}}"><i class="fa fa-edit"></i></button>
												@endif
											@else
												<form action="{{ route('vRestoreProviderConfig') }}" method="post">
													{{csrf_field()}}
													<input hidden name="providerConfigID" value="{{$providerConfig->providerConfigID}}">
													<button class="btn btn-success btn-sm" onclick="return confirm('Are you sure to restore?')" type="submit"> <i class="fa fa-refresh">{{__('adminPanel.restore')}}</i></button>
												</form>
                                            @endif
										</div>

                                        {{-- modal editProviderConfigModal --}}

                                        <div class="modal fade bd-12-modal-lg" id="editProviderConfigModal{{$providerConfig->providerConfigID}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                            <div class="modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="exampleModalLongTitle">{{__('adminPanel.providerConfig')}}</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <span class="text-danger">* {{__('adminPanel.requiredIndication')}}</span><br><br>
                                                        <form action="{{route('vUpdateProviderConfig')}}" class="editProviderConfigModal-form{{$providerConfig->providerConfigID}}" method="POST" >
                                                            {{csrf_field()}}
                                                            <input hidden name="providerConfigID" value="{{$providerConfig->providerConfigID}}">
                                                            <div class="row">
                                                                <div class="col-lg-12">
                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.portalProviderName')}} <span class="text-danger">*</span></label>
                                                                        <div class="form-group">
                                                                            <div class="form-group-select">
                                                                                <select class="form-control" name="portalProviderID">
                                                                                    <option value="">{{__('adminPanel.selectPortalProvider')}}</option>
                                                                                    @foreach ($portalProviderData as $portalProvider)
                                                                                    @if ($portalProvider->PID != "1") {
                                                                                    <option value="{{$portalProvider->PID}}" {{$portalProvider->PID == $providerConfig->portalProviderID ? 'selected':''}}>{{$portalProvider->name}} </option>
                                                                                    @endif
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.followBetSetupID')}}</label>
                                                                        <div class="form-group">
                                                                            <div class="form-group-select">
                                                                                <select class="form-control" name="followBetSetupID">
                                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.followBetSetupID')}}</option>
                                                                                    @foreach ($followBetSetupData as $followBetSetup)
                                                                                    <option value="{{$followBetSetup->PID}}" {{$followBetSetup->PID == $providerConfig->followBetSetupID ? 'selected':''}}>{{$followBetSetup->PID}}</option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.logoutAPICall')}}</label>
                                                                        <div class="form-group">
                                                                            <input class="form-control" name="logoutAPICall" type="number" placeholder="{{__('adminPanel.logoutAPICall')}}" value="{{ $providerConfig->logoutAPICall }}">
                                                                        </div>
																	</div>

																	<div class="form-group">
																		<label class="control-label">{{__('adminPanel.invitationSetup')}}</label>
																		<div class="form-group">
																			<div class="form-group-select">
																				<select class="form-control" name="invitationSetupID">
																					<option value="">{{__('adminPanel.select')}} {{__('adminPanel.invitationSetup')}}</option>
																					@foreach ($invitationSetupData as $invitationSetup)
																					<option value="{{$invitationSetup->PID}}"{{ $invitationSetup->PID == $providerConfig->invitationSetupID ? 'selected':''}} >{{$invitationSetup->name}}</option>
																					@endforeach
																				</select>
																			</div>
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
												var providerConfigID = {{$providerConfig->providerConfigID}};
                                                $(".editProviderConfigModal-form"+providerConfigID).validate({
                                                    rules: {
                                                        portalProviderID: {
                                                            required: true
                                                        }
                                                    },
                                                    messages: {
                                                        portalProviderID: {
                                                            required: "Please Select Portal Provider"
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
		$('.dataTables_filter').prepend('<label>').children().first().append('@lang('adminPanel.searchByFieldName'):<select class="form-control form-control-sm selectable"><option>@lang('adminPanel.all')</option><option value="0">@lang('adminPanel.providerConfigID')</option><option value="1">@lang('adminPanel.portalProviderName')</option><option value="2">@lang('adminPanel.followBetSetupID')</option><option value="4">@lang('adminPanel.createdAt')</option><option value="5">@lang('adminPanel.updatedAt')</option></select>').dataTableFilter(table);
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
