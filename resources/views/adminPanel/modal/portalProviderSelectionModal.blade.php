@section('portalProviderSelectionModal')
<!-- Portal Provider Selection Modal -->
@php
use App\Models\PortalProvider;
$portalProviderModelRef = new PortalProvider();
$sessionData = session(str_replace(".", "_", request()->ip()) . 'ECGames'); //getting PortalProviderUUID from session
$portalProviderData = array();
if ($sessionData['isAllowAll'] == 'true') {
$portalProviderData = $portalProviderModelRef->getAllPortalProvidersById([])->where('PID','!=',1)->select('PID', 'name')->get();
} else if (!isEmpty($sessionData['portalProviderIDs']) && count(explode(',', $sessionData['portalProviderIDs'])) > 0) {
$portalProviderData = $portalProviderModelRef->getAllPortalProvidersById(explode(',', $sessionData['portalProviderIDs']))->where('PID','!=',1)->select('PID', 'name')->get();
}
@endphp
@if ($portalProviderData->count(DB::raw('1')) > 1)
<div class="modal fade" id="portalProviderSelectionModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div>
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">{{__('adminPanel.selectPortalProvider')}}</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">x</span>
					</button>
				</div>
				<div class="modal-body">
					@csrf
					@foreach ($portalProviderData as $eachProvider)
					<input class="portalProviderCheckbox" type="checkbox" value="{{$eachProvider->PID}}"><label>&ensp;{{$eachProvider->name}}</label><br>
					@endforeach
				</div>
				<div class="modal-footer">
					<button id="saveSelectPortalProvider" class="btn btn-primary" type="button">Save changes</button>
					<button class="btn btn-secondary" type="button" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
	let saveSelectPortalProviderRef = $('#saveSelectPortalProvider');
	let portalProviderCheckboxRef = $('.portalProviderCheckbox');
	if(saveSelectPortalProviderRef){
		
		saveSelectPortalProviderRef.click(function () {
			let request = { 
				// "_token": "{{ csrf_token() }}",
				"PortalProviderIDs" : []
			};
			let temp = 0;
			for(let index = 0 ; index < portalProviderCheckboxRef.length ; index++ ){
				if(portalProviderCheckboxRef[index].checked){
					request.PortalProviderIDs[temp] = parseInt(portalProviderCheckboxRef[index].value);
					temp++;
				}
			}
	
			$.ajax({
				url: "{{ route('saveSelectPortalProvider') }}",
				type: 'POST',
				dataType: 'json',
				contentType: 'application/json',
				data: JSON.stringify(request),
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				// dataType: 'json',
				// contentType: 'application/json',
				success: function (response) {
					if(response.status){
						window.location.href = "{{route('updateSession')}}";
					}else{
						alert(response.message);
					}
				},
				error: function () {
					console.log(response);
				}
			});
		});
	}
</script>
@endif
<!-- Portal Provider Selection Modal -->
@endsection