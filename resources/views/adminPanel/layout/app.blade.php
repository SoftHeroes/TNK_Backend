@include('adminPanel.layout.header')
@include('adminPanel.layout.sideBar')
@include('adminPanel.modal.portalProviderSelectionModal')

@include('adminPanel.jsIntoPhp.dataTableMin')
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>EC Admin</title>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
		<!-- Main CSS-->
		<link rel="stylesheet" type="text/css"  href="{{ asset('adminPanel/css/main.css') }}">
		<!-- Font-icon css-->
        <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
        <!-- Flag-icon css-->
        <link href=" https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.2.1/css/flag-icon.css" rel="stylesheet">

	</head>
	<body class="app sidebar-mini">

        <!--Js for alert, need to load it before content-->
        <script src="{{ asset('adminPanel/js/jquery-3.3.1.min.js')}}"></script>
        <script type="text/javascript" src="{{ asset('adminPanel/js/plugins/bootstrap-notify.min.js')}}"></script>

        @yield('dataTableMin')
        
        {{-- Error Message Code --}}
        @if(session()->has('errors'))
        <div>
            @foreach($errors->all() as $error)
            <script>
                $.notify({
                    title: "Error : ",
                    message: "{{ $error }}",
                    icon: 'fa fa-check'
                    },{
                        type: "danger"
                    });
            </script>
            @endforeach
        </div>
        @endif

        {{-- Error Message Code --}}
        @if(session()->has('message'))
        <div>
            @php
                $temp = session()->get('message');
                if(gettype($temp) == 'array'){
                    $message = $temp[array_key_first($temp)];
                }
                else{
                    $message = $temp;
                }
            @endphp

            <script>
                $.notify({
                    title: "Success : ",
                    message: "{{ $message }}",
                    icon: 'fa fa-check'
                    },{
                        type: "success"
                    });
            </script>
        </div>
        @endif

        @yield('portalProviderSelectionModal')
        @yield('sideBar')
		@yield('header')
        @yield('content')

        <!-- Essential javascripts for application to work-->
		<script src="{{ asset('adminPanel/js/popper.min.js')}}"></script>
		<script src="{{ asset('adminPanel/js/bootstrap.min.js')}}"></script>
        <script src="{{ asset('adminPanel/js/main.js')}}"></script>
		<!-- The javascript plugin to display page loading on top-->
        <script src="{{ asset('adminPanel/js/plugins/pace.min.js')}}"></script>

            <!-- form validate JS
		============================================ -->
    <script src="{{ asset('adminPanel/js/form-validation/jquery.form.min.js')}}"></script>
    <script src="{{ asset('adminPanel/js/form-validation/jquery.validate.min.js')}}"></script>
    <script src="{{ asset('adminPanel/js/form-validation/form-active.js')}}"></script>
        
        {{-- <script type="text/javascript" src="{{ asset('adminPanel/js/plugins/jquery.dataTables.min.js')}}"></script> --}}
	</body>
</html>
