@extends('adminPanel.layout.app') @section('content')
<main class="app-content">
	<div class="app-title">
		<div>
			<h1><i class="app-menu__icon fa fa-th-list"></i>{{ __('adminPanel.adminInformation') }}</h1>
			<p>{{ __('adminPanel.dashboardDesc') }}</p>
		</div>
		<ul class="app-breadcrumb breadcrumb side">
			<li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
			<li class="breadcrumb-item active"><a href="{!!route('vGetAdminInformation')!!}">{{ __('adminPanel.adminInformation') }}</a></li>
		</ul>
	</div>
	<!-- Modal -->
	<div class="modal fade bd-12-modal-lg" id="addAdmin" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLongTitle">{{ __('adminPanel.adminInformation') }}</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body">
					<span class="text-danger">* {{ __('adminPanel.requiredIndication') }}</span><br><br>
					<form action="{{route('vAddAdminInformation')}}" class="AddNewAdminInformation-form" method="POST" enctype="multipart/form-data">
						{{csrf_field()}}
						<div class="row">
							<div class="col-lg-12">
								<div class="form-group">
									<label class="control-label">{{ __('adminPanel.firstName') }}</label>
									<div class="form-group">
										<input class="form-control" name="firstName" type="text" placeholder="{{ __('adminPanel.firstName') }}">
									</div>
								</div>
								<div class="form-group">
									<label class="control-label">{{ __('adminPanel.lastName') }}</label>
									<div class="form-group">
										<input class="form-control" name="lastName" type="text" placeholder="{{ __('adminPanel.lastName') }}">
									</div>
								</div>
								<div class="form-group">
									<label class="control-label">{{ __('adminPanel.emails') }} <span class="text-danger">*</span></label>
									<div class="form-group">
										<input class="form-control" name="emailID" type="email" placeholder="{{ __('adminPanel.emails') }}">
									</div>
								</div>
								<div class="form-group">
									<label class="control-label">{{ __('adminPanel.username') }} <span class="text-danger">*</span></label>
									<div class="form-group">
										<input class="form-control" name="username" type="text" placeholder="{{ __('adminPanel.username') }}">
									</div>
								</div>
								<div class="form-group">
									<label class="control-label">{{ __('adminPanel.password') }} <span class="text-danger">*</span></label>
									<div class="form-group">
										<input class="form-control" id="password" name="password" type="password" placeholder="{{__('adminPanel.password')}}">
										<span toggle="#password" class="fa fa-fw fa-eye field-icon toggle-password"></span>
									</div>
								</div>
								<div class="form-group">
									<label class="control-label">{{ __('adminPanel.confirmPassword') }} <span class="text-danger">*</span></label>
									<div class="form-group">
										<input class="form-control" id="confirm_password" name="confirm_password" type="password" placeholder="Confirm Password">
										<span toggle="#confirm_password" class="fa fa-fw fa-eye field-icon toggle-password"></span>
									</div>
								</div>
								<div class="form-group">
									<label class="control-label">{{ __('adminPanel.adminPolicyID') }} <span class="text-danger">*</span></label>
									<div class="form-group">
										<div class="form-group-select">
											<select class="form-control" name="adminPolicyID">
												<option value="">{{ __('adminPanel.selectAdminPolicyID') }}</option>
												@foreach ($adminPolicies as $adminPolicy)
												@if($adminPolicy->PID != "1")
												<option value="{{$adminPolicy->PID}}">{{$adminPolicy->PID}} - {{$adminPolicy->name}}</option>
												@endif
												@endforeach
											</select>
										</div>
									</div>
								</div>
								<div class="form-group">
									<label class="control-label">{{ __('adminPanel.portalProviderName') }} <span class="text-danger">*</span></label>
									<div class="form-group">
										<div class="form-group-select">
											<select class="form-control" name="portalProviderID">
												<option value="">{{ __('adminPanel.selectPortalProvider') }}</option>
												@foreach ($portalProviders as $portalProvider)
												@if ($portalProvider->PID != "1") {
												<option value="{{$portalProvider->PID}}">{{ $portalProvider->name }} </option>
												@endif
												@endforeach
											</select>
										</div>
									</div>
								</div>

								<div class="form-group">
									<label class="control-label">{{ __('adminPanel.accessPolicyID') }} <span class="text-danger">*</span></label>
									<div class="form-group">
										<div class="form-group-select">
											<select class="form-control" name="accessPolicyID">
												<option value="">{{ __('adminPanel.select') }} {{ __('adminPanel.accessPolicyID') }}</option>
												@foreach ($accessPolicy as $accessPolicyData)
												<option value="{{$accessPolicyData->accessPolicyID}}">{{ $accessPolicyData->accessPolicyID }} - {{ $accessPolicyData->name }} </option>
												@endforeach
											</select>
										</div>
									</div>
								</div>

								<div class="form-group">
									<label class="control-label">{{ __('adminPanel.isActive') }} <span class="text-danger">*</span></label>
									<div class="form-group">
										<div class="form-group-select">
											<select class="form-control" name="isActive">
												<option value="">{{ __('adminPanel.selectActiveStatus') }}</option>
												<option value="active">{{__('adminPanel.active')}}</option>
												<option value="inactive">{{__('adminPanel.inactive')}}</option>
											</select>
										</div>
									</div>
								</div>
								<div class="form-group">
									<label for="exampleInputFile">{{ __('adminPanel.fileImage') }}</label>
									<input class="form-control" name="profileImage" type="file" accept="image/jpeg , image/jpg, image/gif, image/png">
								</div>
							</div>
						</div>
						<div class="col-lg-12">
							<button class="btn btn-primary" type="submit" id="btnSubmit">{{ __('adminPanel.save') }}</button>
							<button class="btn btn-danger" type="button" data-dismiss="modal" aria-label="Close">{{ __('adminPanel.cancel') }}</button>
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
								<button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addAdmin">
								{{ __('adminPanel.adminInformation') }} <i class="fa fa-plus"></i>
								</button>
							</div>
						@endif
						<table class="table table-hover table-bordered" id="sampleTable">
							<thead>
								<tr>
									<th>{{ __('adminPanel.adminPID') }}</th>
									<th>{{ __('adminPanel.adminPolicyID') }}</th>
									<th>{{ __('adminPanel.portalProviderID') }}</th>
									<th>{{ __('adminPanel.portalProviderName') }}</th>
									<th>{{ __('adminPanel.Username') }}</th>
									<th>{{ __('adminPanel.firstName') }}</th>
									<th>{{ __('adminPanel.lastName') }}</th>
									<th>{{ __('adminPanel.invalidAttemptsCount') }}</th>
									<th>{{ __('adminPanel.profileImage') }}</th>
									<th>{{ __('adminPanel.accessPolicyID') }}</th>
									<th>{{ __('adminPanel.isActive') }}</th>
									<th>{{ __('adminPanel.lastPasswordResetTime') }}</th>
									<th>{{ __('adminPanel.deletedAt') }}</th>
									<th>{{ __('adminPanel.action') }}</th>
								</tr>
							</thead>
							<tbody>
								@isset($adminData) @foreach ($adminData as $admins) @foreach ($admins as $admin)
								<tr @php if ($admin->deletedAt != Null) { echo "style=background-color:#f37575";} @endphp>
									<td>{{ $admin->adminID }}</td>
									<td>{{ $admin->adminPolicyID }}</td>
									<td>{{ $admin->portalProviderUUID }}</td>
									<td>{{ $admin->portalProviderName }}</td>
									<td>{{ $admin->username }}</td>
									<td>{{ $admin->firstName }}</td>
									<td>{{ $admin->lastName }}</td>
									<td>{{ $admin->invalidAttemptsCount }}</td>
									<td>
										@if($admin->profileImage != "")
										<!-- Button trigger modal -->
										<button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#imageModal{{$admin->adminID}}">
										{{ __('adminPanel.clickViewImage') }}
										</button>
										<!-- Modal -->
										<div class="modal fade bd-12-modal-lg" id="imageModal{{$admin->adminID}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
											<div class="modal-dialog modal-xl" role="document">
												<div class="modal-content">
													<div class="modal-header">
														{{--
														<h5 class="modal-title" id="exampleModalLongTitle">Modal title</h5>
														--}}
														<button type="button" class="close" data-dismiss="modal" aria-label="Close">
														<span aria-hidden="true">&times;</span>
														</button>
													</div>
													<div class="modal-body">
														<img src="/{{$admin->profileImage}}" height="100%" width="100%">
													</div>
												</div>
											</div>
										</div>
										@endif
									</td>
									<td>{{ $admin->accessPolicyID }}</td>
									<td>
										@if($admin->isActive == 'active')
										<span class="badge badge-success" style=" padding: 4px; font-size: 13px;">{{__('adminPanel.active')}}</span> @else
										<span class="badge badge-danger" style=" padding: 4px; font-size: 13px;">{{__('adminPanel.inactive')}}</span> @endif
									</td>
									<td>{{ $admin->lastPasswordResetTime }}</td>
									<td>
										@if($admin->deletedAt != Null)
                                            {{$admin->deletedAt}}
                                        @else
										{{__('adminPanel.null')}}
                                        @endif</td>
									<td>
										<div class="d-flex">
										@if($admin->deletedAt == Null)
											@if($isAllowAll == 'true' || (($isAllowAll == 'false') &&  ($accessibility == 2)))
												<form action="{{ route('vDeleteAdmin') }}" method="post">
													{{csrf_field()}}
													<input hidden name="adminID" value="{{$admin->adminID}}">
													<button class="btn btn-danger btn-sm" onclick="return confirm('@lang('adminPanel.msgDelete')')" type="submit"> <i class="fa fa-trash"></i></button>
												</form>
											@endif
											@if($isAllowAll == 'true' || (($isAllowAll == 'false') && (($accessibility == 1) || ($accessibility == 2))))
												<button type="button" class="btn btn-primary btn-sm ml-1" data-toggle="modal" data-target="#addAdminpolicyModal{{$admin->adminID}}"><i class="fa fa-edit"></i></button>
											@endif
										@else
											<form action="{{ route('vRestoreAdminInformation') }}" method="post">
												{{csrf_field()}}
												<input hidden name="adminID" value="{{$admin->adminID}}">
												<button class="btn btn-success btn-sm" onclick="return confirm('Are you sure to restore?')" type="submit"> <i class="fa fa-refresh">{{__('adminPanel.restore')}}</i></button>
											</form>
										@endif
										</div>
										<!-- Modal -->
										<div class="modal fade bd-12-modal-lg" id="addAdminpolicyModal{{$admin->adminID}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
											<div class="modal-dialog modal-lg" role="document">
												<div class="modal-content">
													<div class="modal-header">
														<h5 class="modal-title" id="exampleModalLongTitle">{{__('adminPanel.editAdminPolicy')}}</h5>
														<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
													</div>
													<div class="modal-body">
														<ul class="nav nav-tabs" role="tablist">
															<li class="nav-item">
																<a class="nav-link active" href="#Information{{$admin->adminID}}" role="tab" data-toggle="tab">{{__('adminPanel.adminInformation')}}</a>
															</li>
															<li class="nav-item">
																<a class="nav-link" href="#changePassword{{$admin->adminID}}" role="tab" data-toggle="tab">{{__('adminPanel.changePassword')}}</a>
															</li>
														</ul>
														<!-- Tab panes -->
														<div class="tab-content">
															<div role="tabpanel" class="tab-pane fade in active show" id="Information{{$admin->adminID}}">
																<span class="text-danger">* {{ __('adminPanel.requiredIndication') }}</span><br><br>
																<form action="{{route('vUpdateAdminInformation')}}" method="POST" class="UpdateAdminInformation-form{{$admin->adminID}}" enctype="multipart/form-data">
																	{{csrf_field()}}
																	<input hidden name="adminID" value="{{$admin->adminID}}">
																	<div class="row">
																		<div class="col-lg-12">
																			<div class="form-group">
																				<label class="control-label">{{ __('adminPanel.firstName') }}</label>
																				<div class="form-group">
																					<input class="form-control" name="firstName" type="text" placeholder="{{ __('adminPanel.firstName') }}" value="{{$admin->firstName}}">
																				</div>
																			</div>
																			<div class="form-group">
																				<label class="control-label">{{ __('adminPanel.lastName') }}</label>
																				<div class="form-group">
																					<input class="form-control" name="lastName" type="text" placeholder="{{ __('adminPanel.lastName') }}" value="{{$admin->lastName}}">
																				</div>
																			</div>
																			<div class="form-group">
																				<label class="control-label">{{__('adminPanel.emails')}} </label>
																				<div class="form-group">
																					<input class="form-control" readonly type="email" placeholder="{{__('adminPanel.emails')}}" value="{{$admin->emailID}}">
																				</div>
																			</div>
																			<div class="form-group">
																				<label class="control-label">{{ __('adminPanel.username') }}</label>
																				<div class="form-group">
																					<input class="form-control" readonly type="text" placeholder="{{ __('adminPanel.username') }}" value="{{$admin->username}}">
																				</div>
																			</div>
																			<div class="form-group">
																				<label class="control-label">{{ __('adminPanel.adminPolicyID') }} <span class="text-danger">*</span></label>
																				<div class="form-group">
																					<div class="form-group-select">
																						<select class="form-control" name="adminPolicyID">
																							<option value="">{{ __('adminPanel.selectAdminPolicyID') }}</option>
																							@foreach ($adminPolicies as $adminPolicy)
																							@if($adminPolicy->PID != "1")
																							<option value="{{$adminPolicy->PID}}" {{$adminPolicy->PID == $admin->adminPolicyID ? 'selected':''}}>{{$adminPolicy->PID}} - {{$adminPolicy->name}}</option>
																							@endif
																							@endforeach
																						</select>
																					</div>
																				</div>
																			</div>
																			<div class="form-group">
																				<label class="control-label">{{ __('adminPanel.portalProviderName') }} <span class="text-danger">*</span></label>
																				<div class="form-group">
																					<div class="form-group-select">
																						<select class="form-control" name="portalProviderID">
																							<option value="">{{ __('adminPanel.selectPortalProvider') }}</option>
																							@foreach ($portalProviders as $portalProvider)
																							@if ($portalProvider->PID != "1") {
																							<option value="{{$portalProvider->PID}}" {{$portalProvider->UUID == $admin->portalProviderUUID ? 'selected':''}}>{{$portalProvider->name}}</option>
																							@endif
																							@endforeach
																						</select>
																					</div>
																				</div>
																			</div>

																			<div class="form-group">
																				<label class="control-label">{{ __('adminPanel.accessPolicyID') }} <span class="text-danger">*</span></label>
																				<div class="form-group">
																					<div class="form-group-select">
																						<select class="form-control" name="accessPolicyID">
																							<option value="">{{ __('adminPanel.select') }} {{ __('adminPanel.accessPolicyID') }}</option>
																							@foreach ($accessPolicy as $accessPolicyData)
																							<option value="{{$accessPolicyData->accessPolicyID}}" {{$accessPolicyData->accessPolicyID == $admin->accessPolicyID ? 'selected':''}}>{{$accessPolicyData->accessPolicyID}} - {{$accessPolicyData->name}} </option>
																							@endforeach
																						</select>
																					</div>
																				</div>
																			</div>


																			<div class="form-group">
																				<label class="control-label">{{ __('adminPanel.isActive') }} <span class="text-danger">*</span></label>
																				<div class="form-group">
																					<div class="form-group-select">
																						<select class="form-control" name="isActive">
																							<option value="">{{ __('adminPanel.selectActiveStatus') }}</option>
																							<option value="active" {{$admin->isActive == 'active' ? 'selected':''}}>{{__('adminPanel.active')}}</option>
																							<option value="inactive" {{$admin->isActive == 'inactive' ? 'selected':''}}>{{__('adminPanel.inactive')}}</option>
																						</select>
																					</div>
																				</div>
																			</div>
																			<div class="form-group">
																				<label for="exampleInputFile">{{__('adminPanel.fileImage')}}</label>
																				<input class="form-control" name="profileImage" type="file" accept="image/jpeg , image/jpg, image/gif, image/png">
																			</div>
																		</div>
																	</div>
																	<div class="col-lg-12">
																		<button class="btn btn-primary" type="submit">{{__('adminPanel.save')}}</button>
																		<button class="btn btn-danger" type="button" data-dismiss="modal" aria-label="Close">{{__('adminPanel.cancel')}}</button>
																	</div>
																</form>
															</div>
															<div role="tabpanel" class="tab-pane fade" id="changePassword{{$admin->adminID}}">
																<form action="{{route('vChangePasswordAdminInformation')}}" class="changePassword changePasswordAdminInformation-form{{$admin->adminID}}" method="POST" enctype="multipart/form-data">
																	{{csrf_field()}}
																	<input hidden name="adminID" value="{{$admin->adminID}}">
																	<div class="row">
																		<div class="col-lg-12">
																			<div class="form-group">
																				<label class="control-label">{{ __('adminPanel.username') }} </label>
																				<div class="form-group">
																					<input class="form-control" readonly type="text" placeholder="{{ __('adminPanel.username') }}" value="{{$admin->username}}">
																				</div>
																			</div>
																			<div class="form-group">
																				<label class="control-label">{{ __('adminPanel.newPassword') }} </label>
																				<div class="form-group">
																					<input class="form-control" id="add_password{{$admin->adminID}}" name="newpassword" type="password" placeholder="{{ __('adminPanel.newPassword') }}">
																					<span toggle="#add_password{{$admin->adminID}}" class="fa fa-fw fa-eye field-icon toggle-password"></span>
																				</div>
																			</div>
																			<div class="form-group">
																				<label class="control-label">{{ __('adminPanel.newConfirmPassword') }}</label>
																				<div class="form-group">
																					<input class="form-control" id="add_confirm_password{{$admin->adminID}}" name="newconfirm_password" type="password" placeholder="{{ __('adminPanel.newConfirmPassword') }}">
																					<span toggle="#add_confirm_password{{$admin->adminID}}" class="fa fa-fw fa-eye field-icon toggle-password"></span>
																					<em id="divCheckPasswordMatch{{$admin->adminID}}"></em>
																				</div>
																			</div>
																		</div>
																	</div>
																	<div class="col-lg-12">
																		<button class="btn btn-primary" type="submit" id="btnSubmitchange{{$admin->adminID}}">{{ __('adminPanel.save') }}</button>
																		<button class="btn btn-danger" type="button" data-dismiss="modal" aria-label="Close">{{ __('adminPanel.cancel') }}</button>
																	</div>
																</form>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<script>
											$(document).ready(function() {
											    var adminID = {{$admin -> adminID}};
											    $(".UpdateAdminInformation-form" + adminID).validate({
											        rules: {
											            adminPolicyID: {
											                required: true
											            },
											            portalProviderID: {
											                required: true
														},
														accessPolicyID: {
															required: true
														},
											            isActive: {
											                required: true
											            }
											        },
											        messages: {
											            adminPolicyID: {
											                required: "Please select admin policy ID"
											            },
											            portalProviderID: {
											                required: "Please select portal provider ID"
														},
														accessPolicyID: {
															required: "Please select Access Policy ID"
														},
											            isActive: {
											                required: "Please select active status"
											            }
											        },

											        errorPlacement: function(error, element) {
											            error.insertAfter(element.parent());
											        }
											    });

											    $(".changePasswordAdminInformation-form" + adminID).validate({
											        rules: {
											            newpassword: {
											                required: true,
											                minlength: 6
											            },
											            newconfirm_password: {
											                required: true
											            }
											        },
											        messages: {
											            newpassword: {
											                required: "Please enter password",
											                minlength: "Password should atleast 6 character in length...!"
											            },
											            newconfirm_password: {
											                required: "Please enter confirm password"
											            }
											        },

											        errorPlacement: function(error, element) {
											            error.insertAfter(element.parent());
											        }
											    });


											    $("#add_password" + adminID + ", #add_confirm_password" + adminID).keyup(function() {
											        var password = $("#add_password" + adminID).val();
											        var confirmPassword = $("#add_confirm_password" + adminID).val();
											        if (password != "" || confirmPassword != "") {
											            if (password == confirmPassword && password > 5) {
											                $("#divCheckPasswordMatch" + adminID).html("Passwords match.").css('color', 'green');
											                $("#btnSubmitchange" + adminID).attr("disabled", false);
											            } else {
											                $("#divCheckPasswordMatch" + adminID).html("Your passwords don`t match. Try again?").css('color', 'red');
											                $('#btnSubmitchange' + adminID).attr("disabled", true);
											            }
											        } else {
											            $("#divCheckPasswordMatch" + adminID).html("");
											            $('#btnSubmitchange').attr("disabled", true);
											        }

											    });

											    $('#btnSubmitchange' + adminID).attr("disabled", true);
											});
										</script>
									</td>
								</tr>
								@endforeach @endforeach @endisset
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
	.AddNewAdminInformation-form .field-icon {
	float: right;
	margin-right: 10px;
	margin-top: -25px;
	position: relative;
	z-index: 2;
	}
	.changePassword .field-icon {
	float: right;
	margin-right: 10px;
	margin-top: -25px;
	position: relative;
	z-index: 2;
	}
	em {
	color: red;
	}
</style>
<script type="text/javascript" src="{{ asset('adminPanel/js/plugins/dataTables.bootstrap.min.js')}}"></script>
<script type="text/javascript">
	$(document).ready(function() {
	    $(".toggle-password").click(function() {
	        $(this).toggleClass("fa-eye fa-eye-slash");
	        var input = $($(this).attr("toggle"));
	        if (input.attr("type") == "password") {
	            input.attr("type", "text");
	        } else {
	            input.attr("type", "password");
	        }
	    });

		// Filter applying
		var table = $('#sampleTable').DataTable({
			responsive: true,
			dom: "<'row'<'col-sm-5'l><'col-sm-7'f>>"+"<'row'<'col-sm-12'tr>>"+"<'row'<'col-sm-5'i><'col-sm-7'p>>",
			language:{
				search: "_INPUT_",
				searchPlaceholder: "{{__('adminPanel.search')}}"
			}
		});
		$('.dataTables_filter').prepend('<label>').children().first().append('@lang('adminPanel.searchByFieldName'):<select class="form-control form-control-sm selectable"><option>@lang('adminPanel.all')</option><option value="0">@lang('adminPanel.adminPID')</option><option value="1">@lang('adminPanel.adminPolicyID')</option><option value="2">@lang('adminPanel.portalProviderID')</option><option value="3">@lang('adminPanel.portalProviderName')</option><option value="4">@lang('adminPanel.Username')</option><option value="5">@lang('adminPanel.firstName')</option><option value="6">@lang('adminPanel.lastName')</option><option value="9">@lang('adminPanel.accessPolicyID')</option><option value="10">@lang('adminPanel.isActive')</option><option value="11">@lang('adminPanel.lastPasswordResetTime')</option></select>').dataTableFilter(table);
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
