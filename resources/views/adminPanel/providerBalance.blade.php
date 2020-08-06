@extends('adminPanel.layout.app') @section('content')
<!-- Bet History content start -->
<main class="app-content">
	<div class="app-title">
		<div>
			<h1><i class="app-menu__icon fa fa-th-list"></i> {{__('adminPanel.providerRequestList')}}</h1>
			<p>{{__('adminPanel.dashboardDesc')}}</p>
		</div>
		<ul class="app-breadcrumb breadcrumb side">
			<li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
			<li class="breadcrumb-item active">{{__('adminPanel.provider')}}</li>
			<li class="breadcrumb-item active"><a href="{!!route('vProviderBalance')!!}">{{__('adminPanel.providerRequestList')}}</a></li>
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
									<th>No</th>
									@isset($policyCheckAccess)
										@if($policyCheckAccess)
											<th>{{__('adminPanel.portalProviderID')}} </th>
											<th>{{__('adminPanel.portalProviderName')}} </th>
										@endif
									@endisset
									<th> {{__('adminPanel.requestDescription')}} </th>
									<th> {{__('adminPanel.Images')}} </th>
									<th> {{__('adminPanel.Amount')}} </th>
									<th> {{__('adminPanel.currencyName')}} </th>
									<th> {{__('adminPanel.rate')}} </th>
									<th> {{__('adminPanel.chipValue')}} </th>
									<th> {{__('adminPanel.datetime')}} </th>
									<th> {{__('adminPanel.Status')}} </th>
									@isset($policyCheckAccess)
										@if($policyCheckAccess)
											<th>{{__('adminPanel.accept')}}</th>
										@endif
									@endisset
								</tr>
							</thead>
							<tbody>
								@if(isset($creditRequestData) && $creditRequestData != null)
									@foreach ($creditRequestData as $key => $creditRequest)
										<tr>
											<td>{{$key+1}}</td>
											@isset($policyCheckAccess)
												@if($policyCheckAccess)
													<td> {{$creditRequest->portalProviderUUID}} </td>
													<td> {{$creditRequest->portalProviderName}} </td>
												@endif
											@endisset
											<td>
												<a data-toggle="modal" href="#showNotificationModal{{$creditRequest->creditRequestPID}}">
													{{substr($creditRequest->creditRequestDescription,0,20)}} {{strlen($creditRequest->creditRequestDescription) > 20 ? '...':''}}
												</a>

												<!-- Modal -->
                                                <div class="modal fade bd-12-modal-lg" id="showNotificationModal{{$creditRequest->creditRequestPID}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="exampleModalLongTitle">{{__('adminPanel.portalProviderName')}}: {{$creditRequest->portalProviderName}}</h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                            </div>
                                                            <div class="modal-body">
																<div class="row">
																	<div class="col-lg-12">
																		<div class="timeline-post">
																			<div class="post-content">
                                                                            	<p>{{$creditRequest->creditRequestDescription}}</p>
                                                                            </div>
																		</div>
																	</div>
																</div>
																<div class="modal-footer">
																	<button class="btn btn-primary" type="button" data-dismiss="modal" aria-label="Close">{{__('adminPanel.cancel')}}</button>
																</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
											</td>
											<td>
												<!-- Button trigger modal -->
												<button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#imageModal{{$creditRequest->creditRequestPID}}">
												{{__('adminPanel.clickViewImage')}}
												</button>
												<!-- Modal -->
												<div class="modal fade bd-12-modal-lg" id="imageModal{{$creditRequest->creditRequestPID}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
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
																<img src="/images/{{$creditRequest->creditRequestImage}}" height="100%" width="100%">
															</div>
														</div>
													</div>
												</div>
											</td>
											<td> {{$creditRequest->amount}} </td>
											<td> {{$creditRequest->currencyName}} </td>
											<td> {{$creditRequest->rate}} </td>
											<td> {{$creditRequest->chipValue}} </td>
											<td> {{$creditRequest->createdAt}} </td>
											<td>
												@if($creditRequest->requestStatus == 1)
													<span class="badge badge-success">{{__('adminPanel.accepted')}}</span>
												@elseif($creditRequest->requestStatus == 2)
													<span class="badge badge-danger">{{__('adminPanel.canceled')}}</span>
												@else
													<span class="badge badge-warning">{{__('adminPanel.pending')}}</span>
												@endif
											</td>
											@isset($policyCheckAccess)
												@if($policyCheckAccess)
													<td>
														<div class="d-flex">
															<form action="{{route('CreditRequestManagement')}}" method="post">
																{{csrf_field()}}
																<input name="creditRequestPID" hidden value="{{$creditRequest->creditRequestPID}}">
																@if($creditRequest->requestStatus == 0)
																	<button class="btn btn-success btn-sm" name="action" value="accept" type="submit">{{__('adminPanel.accept')}}</button>
																	<button class="btn btn-danger btn-sm" name="action" value="cancel" type="submit">{{__('adminPanel.cancel')}}</button>
																@endif
															</form>
														</div>
													</td>
												@endif
											@endisset
										</tr>
									@endforeach
								@endisset
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
	$(document).ready(function(){
		var table = $('#sampleTable').DataTable({
			responsive: true,
			dom: "<'row'<'col-sm-5'l><'col-sm-7'f>>"+"<'row'<'col-sm-12'tr>>"+"<'row'<'col-sm-5'i><'col-sm-7'p>>",
			language:{
				search: "_INPUT_",
				searchPlaceholder: "{{__('adminPanel.search')}}"
			}
		});
		$('.dataTables_filter').prepend('<label>').children().first().append('@lang('adminPanel.searchByFieldName'):<select class="form-control form-control-sm selectable"><option>@lang('adminPanel.all')</option><option value="1">@lang('adminPanel.portalProviderID')</option><option value="2">@lang('adminPanel.portalProviderName')</option><option value="3">@lang('adminPanel.requestDescription')</option><option value="5">@lang('adminPanel.Amount')</option><option value="6">@lang('adminPanel.currencyName')</option><option value="7">@lang('adminPanel.datetime')</option><option value="8">@lang('adminPanel.Status')</option></select>').dataTableFilter(table);
		$('.dataTables_filter').append('<br><button class="btn" onClick="window.location.reload();"><i class="fa fa-refresh" aria-hidden="true"></i>@lang('adminPanel.refresh')</button>').dataTableFilter(table);

	});
</script>
<style>
.post-content {
	word-wrap : break-word;
}
</style>
<!-- Bet History content end -->
@endsection
