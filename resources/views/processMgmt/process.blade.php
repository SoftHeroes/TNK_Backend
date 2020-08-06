<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>EC : Process Management</title>
		<link rel="icon" href="{{ asset('ProcessMgmt/images/favicon.png') }}" type="image/png" sizes="16x16">

		<link rel="stylesheet" href="{{asset('ProcessMgmt/css/font-awesome.min.css') }}" rel="stylesheet">
		<link rel="stylesheet" href="{{asset('ProcessMgmt/css/style.css')}}">

	</head>
	<body>
		<header>
			<img class="logo" src="{{asset('ProcessMgmt/images/logo.png')}}">
		</header>
		<div class="container-fluid">
			<h3>Process Management Table</h3>
			<div class="table-responsive text-center">
			
				{{-- Error Message Code --}}
				@if(session()->has('errors'))
				<div class="alert-danger center">
					<strong>Whoops!</strong> There were some problems with your input.
					<br />
					<ul>
						@foreach($errors->all() as $error)
						<li>{{ $error }}</li>
						@endforeach
					</ul>
				</div>
				@endif
				{{-- Success Message Code --}}
				@if(session()->has('message'))
				<div class="alert alert-success center">
					{{ session()->get('message') }}
				</div>
				@endif

				<table align="center">
					<thead>
						<tr>
							<th>S.No.</th>
							<th>Name</th>
							<th>Status</th>
							<th>System Process ID</th>
							<th>Last Started At</th>
							<th>Last Stop At</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						@if( isset($allProcess) )
						@foreach ($allProcess as $singleProcess)
						<tr class="{!! $singleProcess->status ? 'running' : 'notRunning' !!}">
							<td>{{ $singleProcess->no }}</td>
							<td>{{ $singleProcess->name }}</td>
							<td>{{ $singleProcess->status ? 'Running' : 'Not Running' }}</td>
							<td>{{ $singleProcess->systemPID}}</td>
							<td>{{ $singleProcess->lastStartedAt}}</td>
							<td>{{ $singleProcess->lastStopAt}}</td>
							<td class="action-row">
								<a href="{!! $singleProcess->status ? '#' : route('startProcess',$singleProcess->PID) !!}"><i class="fa fa-play-circle" aria-hidden="true"></i></a>
								<a href="{!! $singleProcess->status ? route('stopProcess',$singleProcess->PID) : '#' !!}"><i class="fa fa-stop-circle" aria-hidden="true"></i></a>
								{{--  <a href="#"><i class="fa fa-refresh" aria-hidden="true"></i></a>  --}}
							</td>
						</tr>
						@endforeach
						@endif
					</tbody>
				</table>
			</div>
		</div>
	</body>
</html>