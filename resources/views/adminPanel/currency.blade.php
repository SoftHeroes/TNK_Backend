@extends('adminPanel.layout.app') @section('content')
<main class="app-content">
	<div class="app-title">
		<div>
			<h1><i class="app-menu__icon fa fa-th-list"></i>{{__('adminPanel.currency')}}</h1>
			<p>{{__('adminPanel.dashboardDesc')}}</p>
		</div>
		<ul class="app-breadcrumb breadcrumb side">
			<li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
			<li class="breadcrumb-item active"><a href="{!!route('vCurrency')!!}">{{__('adminPanel.currency')}}</a></li>
		</ul>
	</div>
	<!-- Modal -->
	<div class="modal fade bd-12-modal-lg" id="createCurrencyModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLongTitle">{{__('adminPanel.newCurrency')}}</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body">
					<span class="text-danger">* {{__('adminPanel.requiredIndication')}}</span><br><br>
					<form action="{{route('vCreateCurrency')}}" class="createCurrency-form" method="POST">
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
									<label class="control-label">{{__('adminPanel.rate')}} <span class="text-danger">*</span></label>
									<div class="form-group">
										<input class="form-control" name="rate" type="number" placeholder="{{__('adminPanel.rate')}}">
									</div>
                                </div>

                                <div class="form-group">
									<label class="control-label">{{__('adminPanel.symbol')}} <span class="text-danger">*</span></label>
									<div class="form-group">
										<input class="form-control" name="symbol" type="text" placeholder="{{__('adminPanel.symbol')}}">
									</div>
                                </div>

                                <div class="form-group">
									<label class="control-label">{{__('adminPanel.abbreviation')}} <span class="text-danger">*</span></label>
									<div class="form-group">
										<input class="form-control" name="abbreviation" type="text" placeholder="{{__('adminPanel.abbreviation')}}">
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
                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createCurrencyModal">
                                {{__('adminPanel.newCurrency')}} <i class="fa fa-plus"></i>
                                </button>
                            </div>
                        @endif
						<table class="table table-hover table-bordered" id="sampleTable">
							<thead>
								<tr>
									<th>{{__('adminPanel.name')}}</th>
									<th>{{__('adminPanel.rate')}}</th>
									<th>{{__('adminPanel.symbol')}}</th>
									<th>{{__('adminPanel.abbreviation')}}</th>
                                    <th>{{__('adminPanel.isActive')}}</th>
                                    <th>{{__('adminPanel.createdAt')}}</th>
                                    <th>{{__('adminPanel.updatedAt')}}</th>
                                    <th>{{__('adminPanel.deletedAt')}}</th>
                                    <th>{{ __('adminPanel.action') }}</th>
								</tr>
							</thead>
							<tbody>
								@isset($currencyData) @foreach ($currencyData as $currency)
								<tr @php if ($currency->deletedAt != Null) { echo "style=background-color:#f37575";} @endphp>
									<td>{{ $currency->name }}</td>
									<td>{{ $currency->rate }}</td>
									<td>{{ $currency->symbol }}</td>
									<td>{{ $currency->abbreviation }}</td>
                                    <td>
                                        @if($currency->isActive == 'active')
										    <span class="badge badge-success" style=" padding: 4px; font-size: 13px;">{{__('adminPanel.active')}}</span> 
                                        @else
										    <span class="badge badge-danger" style=" padding: 4px; font-size: 13px;">{{__('adminPanel.inactive')}}</span> 
                                        @endif
                                    </td>
                                    <td>{{ $currency->createdAt }}</td>
                                    <td>{{ $currency->updatedAt }}</td>
                                    <td>
                                        @if($currency->deletedAt != Null)
                                            {{$currency->deletedAt}}
                                        @else
                                        {{__('adminPanel.null')}}
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex">
                                            @if($currency->deletedAt == Null)
                                                @if($isAllowAll == 'true' || (($isAllowAll == 'false') && ($accessibility == 2)))
                                                    <form action="{{ route('vDeleteCurrency') }}" method="post">
                                                        {{csrf_field()}}
                                                        <input hidden name="currencyID" value="{{$currency->PID}}">
                                                        <button class="btn btn-danger btn-sm" onclick="return confirm('@lang('adminPanel.msgDelete')')" type="submit"> <i class="fa fa-trash"></i></button>
                                                    </form>
                                                @endif
                                                @if($isAllowAll == 'true' || (($isAllowAll == 'false') && (($accessibility == 1) || ($accessibility == 2))))
                                                    <button type="button" class="btn btn-primary btn-sm ml-1" data-toggle="modal" data-target="#editCurrencyModal{{$currency->PID}}"><i class="fa fa-edit"></i></button>
                                                @endif
                                            @else
                                                <form action="{{ route('vRestoreCurrency') }}" method="post">
                                                    {{csrf_field()}}
                                                    <input hidden name="currencyID" value="{{$currency->PID}}">
                                                    <button class="btn btn-success btn-sm" onclick="return confirm('Are you sure to restore?')" type="submit"> <i class="fa fa-refresh">{{__('adminPanel.restore')}}</i></button>
                                                </form>
                                            @endif
                                        </div>

                                        {{-- modal editCurrencyModal --}}

                                        <div class="modal fade bd-12-modal-lg" id="editCurrencyModal{{$currency->PID}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                            <div class="modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="exampleModalLongTitle">{{__('adminPanel.editCurrency')}}</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <span class="text-danger">* {{__('adminPanel.requiredIndication')}}</span><br><br>
                                                        <form action="{{route('vUpdateCurrency')}}" class="editCurrencyModal-form{{$currency->PID}}" method="POST" >
                                                            {{csrf_field()}}
                                                            <input hidden name="currencyID" value="{{$currency->PID}}">
                                                            <div class="row">
                                                                <div class="col-lg-12">

                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.name')}} <span class="text-danger">*</span></label>
                                                                        <div class="form-group">
                                                                            <input class="form-control" name="name" type="text" placeholder="{{__('adminPanel.name')}}" value="{{ $currency->name }}">
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.rate')}} <span class="text-danger">*</span></label>
                                                                        <div class="form-group">
                                                                            <input class="form-control" name="rate" type="number" placeholder="{{__('adminPanel.rate')}}" value="{{ $currency->rate }}">
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.symbol')}} <span class="text-danger">*</span></label>
                                                                        <div class="form-group">
                                                                            <input class="form-control" name="symbol" type="text" placeholder="{{__('adminPanel.symbol')}}" value="{{ $currency->symbol }}">
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.abbreviation')}} <span class="text-danger">*</span></label>
                                                                        <div class="form-group">
                                                                            <input class="form-control" name="abbreviation" type="text" placeholder="{{__('adminPanel.abbreviation')}}" value="{{ $currency->abbreviation }}">
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.isActive')}} <span class="text-danger">*</span></label>
                                                                        <div class="form-group">
                                                                            <div class="form-group-select">
                                                                                <select class="form-control" name="isActive">
                                                                                    <option value="">{{__('adminPanel.selectActiveStatus')}}</option>
                                                                                    <option value="active" {{$currency->isActive == 'active' ? 'selected':''}}>{{__('adminPanel.active')}}</option>
                                                                                    <option value="inactive" {{$currency->isActive == 'inactive' ? 'selected':''}}>{{__('adminPanel.inactive')}}</option>
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
											    var currencyID = {{$currency->PID}};
                                                $(".editCurrencyModal-form"+currencyID).validate({
                                                    rules: {
                                                        name: {
                                                            required: true
                                                        },
                                                        rate: {
                                                            required: true,
                                                            number: true,
                                                            min: 0
                                                        },
                                                        isActive: {
                                                            required: true
                                                        },
                                                        symbol: {
                                                            required: true
                                                        },
                                                        abbreviation: {
                                                            required: true
                                                        }
                                                    },
                                                    messages: {
                                                        name: {
                                                            required: "Please Enter Name"
                                                        },
                                                        rate: {
                                                            required: "Please Enter Rate"
                                                        },
                                                        isActive: {
                                                            required: "Please Select Active Status"
                                                        },
                                                        symbol: {
                                                            required: "Please Enter Symbol"
                                                        },
                                                        abbreviation: {
                                                            required: "Please Enter Abbreviation"
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

<script type="text/javascript" src="{{ asset('adminPanel/js/plugins/dataTables.bootstrap.min.js')}}"></script>
<script type="text/javascript">
$(document).ready(function() {
	var table = $('#sampleTable').DataTable({
        responsive: true,
        dom: "<'row'<'col-sm-5'l><'col-sm-7'f>>"+"<'row'<'col-sm-12'tr>>"+"<'row'<'col-sm-5'i><'col-sm-7'p>>",
        language:{
            search: "_INPUT_",
            searchPlaceholder: "{{__('adminPanel.search')}}"
        }
    });
    $('.dataTables_filter').prepend('<label>').children().first().append('@lang('adminPanel.searchByFieldName'):<select class="form-control form-control-sm selectable"><option>@lang('adminPanel.all')</option><option value="0">@lang('adminPanel.name')</option><option value="1">@lang('adminPanel.rate')</option><option value="2">@lang('adminPanel.symbol')</option><option value="3">@lang('adminPanel.abbreviation')</option><option value="4">@lang('adminPanel.createdAt')</option><option value="5">@lang('adminPanel.updatedAt')</option><option value="6">@lang('adminPanel.isActive')</option></select>').dataTableFilter(table);
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
