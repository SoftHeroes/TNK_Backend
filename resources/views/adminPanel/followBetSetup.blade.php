@extends('adminPanel.layout.app') @section('content')
<main class="app-content">
	<div class="app-title">
		<div>
			<h1><i class="app-menu__icon fa fa-th-list"></i>{{__('adminPanel.followBetSetup')}}</h1>
			<p>{{__('adminPanel.dashboardDesc')}}</p>
		</div>
		<ul class="app-breadcrumb breadcrumb side">
			<li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
			<li class="breadcrumb-item active"><a href="{!!route('vFollowBetSetup')!!}">{{__('adminPanel.followBetSetup')}}</a></li>
		</ul>
	</div>
	<!-- Modal -->
	<div class="modal fade bd-12-modal-lg" id="createfollowBetSetupModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLongTitle">{{__('adminPanel.newFollowBetSetup')}}</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body">
					<span class="text-danger">* {{__('adminPanel.requiredIndication')}}</span><br><br>
					<form action="{{route('vCreateFollowBetSetup')}}" class="createFollowBetSetup-form" method="POST">
						{{csrf_field()}}
						<div class="row">
							<div class="col-lg-12">

                                <div class="form-group">
                                    <label class="control-label">{{__('adminPanel.followBetRuleID')}}</label>
                                    <div class="form-group">
                                        <div class="form-group-select">
                                            <select class="form-control" id="followBetRuleIDSelect" multiple="" name="followBetRuleID[]">
                                                <optgroup label="{{__('adminPanel.select')}} {{__('adminPanel.followBetRuleID')}}">
                                                    @foreach ($followBetRuleData as $key => $followBetRule)
                                                    @if($followBetRule->type == '1')
                                                        <option value="{{$followBetRule->PID}}">{{$followBetRule->PID}} - {{$followBetRule->name}}</option>
                                                    @endif
                                                    @endforeach
                                                </optgroup>
                                              </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label">{{__('adminPanel.unFollowBetRuleID')}}</label>
                                    <div class="form-group">
                                        <div class="form-group-select">
                                            <select class="form-control" id="unFollowBetRuleIDSelect" multiple="" name="unFollowBetRuleID[]">
                                                <optgroup label="{{__('adminPanel.select')}} {{__('adminPanel.unFollowBetRuleID')}}">
                                                    @foreach ($followBetRuleData as $key => $followBetRule)
                                                    @if($followBetRule->type == '2')
                                                        <option value="{{$followBetRule->PID}}">{{$followBetRule->PID}} -> {{$followBetRule->name}}</option>
                                                    @endif
                                                    @endforeach
                                                </optgroup>
                                              </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label">{{__('adminPanel.minFollowBetRuleSelect')}}</label>
                                    <div class="form-group">
                                        <input class="form-control" name="minFollowBetRuleSelect" type="number" placeholder="{{__('adminPanel.minFollowBetRuleSelect')}}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label">{{__('adminPanel.maxFollowBetRuleSelect')}}</label>
                                    <div class="form-group">
                                        <input class="form-control" name="maxFollowBetRuleSelect" type="number" placeholder="{{__('adminPanel.maxFollowBetRuleSelect')}}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label">{{__('adminPanel.minUnFollowBetRuleSelect')}}</label>
                                    <div class="form-group">
                                        <input class="form-control" name="minUnFollowBetRuleSelect" type="number" placeholder="{{__('adminPanel.minUnFollowBetRuleSelect')}}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label">{{__('adminPanel.maxUnFollowBetRuleSelect')}}</label>
                                    <div class="form-group">
                                        <input class="form-control" name="maxUnFollowBetRuleSelect" type="number" placeholder="{{__('adminPanel.maxUnFollowBetRuleSelect')}}">
                                    </div>
                                </div>


                                <div class="form-group">
									<label class="control-label">{{__('adminPanel.isActive')}} <span class="text-danger">*</span></label>
									<div class="form-group">
										<div class="form-group-select">
											<select class="form-control" name="isActive">
												<option value="">{{__('adminPanel.selectActiveStatus')}}</option>
												<option value="active">{{__('adminPanel.active')}}</option>
												<option value="inactive">{{__('adminPanel.inactive')}}</option>
											</select>
										</div>
									</div>
								</div>

							</div>
						</div>
						<div class="col-lg-12">
							<button class="btn btn-primary" type="submit" id="btnSubmit">{{__('adminPanel.create')}}</button>
							<button class="btn btn-danger" type="reset" data-dismiss="modal" aria-label="Close">{{__('adminPanel.cancel')}}</button>
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
                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createfollowBetSetupModal">
                                {{__('adminPanel.newFollowBetSetup')}} <i class="fa fa-plus"></i>
                                </button>
                            </div>
                        @endif
						<table class="table table-hover table-bordered" id="sampleTable">
							<thead>
								<tr>
									<th>{{__('adminPanel.followBetRuleID')}}</th>
									<th>{{__('adminPanel.unFollowBetRuleID')}}</th>
									<th>{{__('adminPanel.minFollowBetRuleSelect')}}</th>
									<th>{{__('adminPanel.maxFollowBetRuleSelect')}}</th>
									<th>{{__('adminPanel.minUnFollowBetRuleSelect')}}</th>
									<th>{{__('adminPanel.maxUnFollowBetRuleSelect')}}</th>
                                    <th>{{__('adminPanel.isActive')}}</th>
                                    <th>{{__('adminPanel.createdAt')}}</th>
                                    <th>{{__('adminPanel.updatedAt')}}</th>
                                    <th>{{__('adminPanel.deletedAt')}}</th>
                                    <th>{{ __('adminPanel.action') }}</th>
								</tr>
							</thead>
							<tbody>
								@isset($followBetSetupData) @foreach ($followBetSetupData as $followBetSetup)
								<tr @php if ($followBetSetup->deletedAt != Null) { echo "style=background-color:#f37575";} @endphp>
                                    <td>{{ $followBetSetup->followBetRuleID }}</td>
                                    <td>{{$followBetSetup->unFollowBetRuleID}}</td>
									<td>{{ $followBetSetup->minFollowBetRuleSelect }}</td>
									<td>{{ $followBetSetup->maxFollowBetRuleSelect }}</td>
                                    <td>{{ $followBetSetup->minUnFollowBetRuleSelect }}</td>
                                    <td>{{ $followBetSetup->maxUnFollowBetRuleSelect }}</td>
                                    <td>	@if($followBetSetup->isActive == 'active')
										<span class="badge badge-success" style=" padding: 4px; font-size: 13px;">{{__('adminPanel.active')}}</span> @else
										<span class="badge badge-danger" style=" padding: 4px; font-size: 13px;">{{__('adminPanel.inactive')}}</span> @endif
                                    </td>
                                    <td>{{ $followBetSetup->createdAt }}</td>
                                    <td>{{ $followBetSetup->updatedAt }}</td>
                                    <td>
										@if($followBetSetup->deletedAt != Null)
                                            {{$followBetSetup->deletedAt}}
                                        @else
                                        {{__('adminPanel.null')}}
                                        @endif
                                    </td>
									<td>
                                        <div class="d-flex">
                                            @if($followBetSetup->deletedAt == Null)
                                                @if($isAllowAll == 'true' || (($isAllowAll == 'false') && ($accessibility == 2)))
                                                    <form action="{{ route('vDeleteFollowBetSetup') }}" method="post">
                                                        {{csrf_field()}}
                                                        <input hidden name="followBetSetupID" value="{{$followBetSetup->PID}}">
                                                        <button class="btn btn-danger btn-sm" onclick="return confirm('@lang('adminPanel.msgDelete')')" type="submit"> <i class="fa fa-trash"></i></button>
                                                    </form>
                                                @endif
                                                @if($isAllowAll == 'true' || (($isAllowAll == 'false') && (($accessibility == 1) || ($accessibility == 2))))
                                                    <button type="button" class="btn btn-primary btn-sm ml-1" data-toggle="modal" data-target="#editFollowBetSetupModal{{$followBetSetup->PID}}"><i class="fa fa-edit"></i></button>
                                                @endif
                                            @else
                                                <form action="{{ route('vRestoreFollowBetSetup') }}" method="post">
                                                    {{csrf_field()}}
                                                    <input hidden name="followBetSetupID" value="{{$followBetSetup->PID}}">
                                                    <button class="btn btn-success btn-sm" onclick="return confirm('Are you sure to restore?')" type="submit"> <i class="fa fa-refresh">{{__('adminPanel.restore')}}</i></button>
                                                </form>
                                            @endif
                                        </div>

                                        {{-- modal editFollowBetSetupModal --}}

                                        <div class="modal fade bd-12-modal-lg" id="editFollowBetSetupModal{{$followBetSetup->PID}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                            <div class="modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="exampleModalLongTitle">{{__('adminPanel.editFollowBetSetup')}}</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <span class="text-danger">* {{__('adminPanel.requiredIndication')}}</span><br><br>
                                                        <form action="{{route('vUpdateFollowBetSetup')}}" class="editFollowBetSetupModal-form{{$followBetSetup->PID}}" method="POST" >
                                                            {{csrf_field()}}
                                                            <input hidden name="followBetSetupID" value="{{$followBetSetup->PID}}">
                                                            <div class="row">
                                                                <div class="col-lg-12">

                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.followBetRuleID')}}</label>
                                                                        <div class="form-group">
                                                                            <div class="form-group-select">
                                                                                <select class="form-control" id="followBetRuleIDSelect{{$followBetSetup->PID}}" multiple="" name="followBetRuleID[]">
                                                                                    <optgroup label="{{__('adminPanel.select')}} {{__('adminPanel.followBetRuleID')}}">
                                                                                        @foreach ($followBetRuleData as $key => $followBetRule)
                                                                                        @if($followBetRule->type == '1')
                                                                                             @php $followBetSetupData = explode(',', $followBetSetup->followBetRuleID); @endphp
                                                                                                <option
                                                                                                    value="{{$followBetRule->PID}}"
                                                                                                        <?php
foreach ($followBetSetupData as $BetSetup) {
    if ($BetSetup == $followBetRule->PID) {
        echo "Selected";
    }
}
?>>
                                                                                                        {{$followBetRule->PID}} - {{$followBetRule->name}}
                                                                                                        </option>
                                                                                        @endif
                                                                                        @endforeach
                                                                                    </optgroup>
                                                                                  </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.unFollowBetRuleID')}}</label>
                                                                        <div class="form-group">
                                                                            <div class="form-group-select">
                                                                                <select class="form-control" id="unFollowBetRuleIDSelect{{$followBetSetup->PID}}" multiple="" name="unFollowBetRuleID[]">
                                                                                    <optgroup label="{{__('adminPanel.select')}} {{__('adminPanel.unFollowBetRuleID')}}">
                                                                                        @foreach ($followBetRuleData as $key => $followBetRule)
                                                                                            @if($followBetRule->type == '2')
                                                                                                @php $followBetSetupData = explode(',', $followBetSetup->unFollowBetRuleID); @endphp
                                                                                                    <option
                                                                                                        value="{{$followBetRule->PID}}"
                                                                                                            <?php
foreach ($followBetSetupData as $BetSetup) {
    if ($BetSetup == $followBetRule->PID) {
        echo "Selected";
    }
}
?>>
                                                                                                            {{$followBetRule->PID}} - {{$followBetRule->name}}
                                                                                                        </option>
                                                                                            @endif
                                                                                        @endforeach
                                                                                    </optgroup>
                                                                                  </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.minFollowBetRuleSelect')}}</label>
                                                                        <div class="form-group">
                                                                            <input class="form-control" name="minFollowBetRuleSelect" type="number" placeholder="{{__('adminPanel.minFollowBetRuleSelect')}}" value="{{ $followBetSetup->minFollowBetRuleSelect }}">
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.maxFollowBetRuleSelect')}}</label>
                                                                        <div class="form-group">
                                                                            <input class="form-control" name="maxFollowBetRuleSelect" type="number" placeholder="{{__('adminPanel.maxFollowBetRuleSelect')}}" value="{{ $followBetSetup->maxFollowBetRuleSelect }}">
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.minUnFollowBetRuleSelect')}}</label>
                                                                        <div class="form-group">
                                                                            <input class="form-control" name="minUnFollowBetRuleSelect" type="number" placeholder="{{__('adminPanel.minUnFollowBetRuleSelect')}}" value="{{ $followBetSetup->minUnFollowBetRuleSelect }}">
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.maxUnFollowBetRuleSelect')}}</label>
                                                                        <div class="form-group">
                                                                            <input class="form-control" name="maxUnFollowBetRuleSelect" type="number" placeholder="{{__('adminPanel.maxUnFollowBetRuleSelect')}}" value="{{ $followBetSetup->maxUnFollowBetRuleSelect }}">
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.isActive')}} <span class="text-danger">*</span></label>
                                                                        <div class="form-group">
                                                                            <div class="form-group-select">
                                                                                <select class="form-control" name="isActive">
                                                                                    <option value="">{{__('adminPanel.selectActiveStatus')}}</option>
                                                                                    <option value="active" {{$followBetSetup->isActive == 'active' ? 'selected':''}}>{{__('adminPanel.active')}}</option>
                                                                                    <option value="inactive" {{$followBetSetup->isActive == 'inactive' ? 'selected':''}}>{{__('adminPanel.inactive')}}</option>
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
                                                var PID = {{$followBetSetup->PID}};

                                                $(".editFollowBetSetupModal-form"+PID).validate({
                                                    rules: {
                                                        isActive: {
                                                            required: true
                                                        }
                                                    },
                                                    messages: {
                                                        isActive: {
                                                            required: "Please Select Active Status"
                                                        }
                                                    },

                                                    errorPlacement: function(error, element) {
                                                        error.insertAfter(element.parent());
                                                    }
                                                });

                                                $('#followBetRuleIDSelect'+PID).select2();
                                                $('#unFollowBetRuleIDSelect'+PID).select2();
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
<script type="text/javascript" src="{{ asset('adminPanel/js/plugins/select2.min.js')}}"></script>
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
        $('.dataTables_filter').prepend('<label>').children().first().append('@lang('adminPanel.searchByFieldName'):<select class="form-control form-control-sm selectable"><option>@lang('adminPanel.all')</option><option value="0">@lang('adminPanel.followBetRuleID')</option><option value="1">@lang('adminPanel.unFollowBetRuleID')</option><option value="6">@lang('adminPanel.createdAt')</option><option value="7">@lang('adminPanel.updatedAt')</option><option value="8">@lang('adminPanel.isActive')</option></select>').dataTableFilter(table);
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

        $('#followBetRuleIDSelect').select2();
        $('#unFollowBetRuleIDSelect').select2();
    });
</script>
<style>
    .select2-search__field {
        width: 54em !important;
    }
</style>
<!-- game History content end -->
@endsection