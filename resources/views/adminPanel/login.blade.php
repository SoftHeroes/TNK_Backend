<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- Main CSS-->
		<link rel="stylesheet" type="text/css" href="{{ asset('adminPanel/css/main.css') }}">
		<!-- Font-icon css-->
		<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
        <title>Login - EC Admin</title>
		 <link href=" https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.2.1/css/flag-icon.css" rel="stylesheet">
	</head>
	<body>
        <script src="{{ asset('adminPanel/js/jquery-3.3.1.min.js')}}"></script>
        <script type="text/javascript" src="{{ asset('adminPanel/js/plugins/bootstrap-notify.min.js')}}"></script>
        <section class="material-half-bg">
			<div class="cover"></div>
		</section>
		<section class="login-content">
			<div class="logo">
				<h1>EC Games</h1>
            </div>

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

			{{-- Success Message Code --}}
			@if(session()->has('message'))
			<div>
				<script>
					$.notify({
					    title: "Success : ",
					    message: "{{ session()->get('message') }}",
					    icon: 'fa fa-check'
					    },{
					        type: "success"
					    });
				</script>
			</div>
            @endif


			<div class="login-box">
				<form class="login-form" method="POST" action="{!! route('vAdminLogin') !!}">
					{{csrf_field()}}
					<h3 class="login-head"><i class="fa fa-lg fa-fw fa-user"></i>{{__('adminPanel.signin')}}</h3>
					<div class="form-group">
						<label class="control-label">{{__('adminPanel.username')}}</label>
						<input class="form-control" name="username" required="required" type="text" placeholder="{{__('adminPanel.username')}}" autofocus>
					</div>
					<div class="form-group">
						<label class="control-label">{{__('adminPanel.password')}}</label>
                        <input class="form-control" id="password" name="password" required="required" type="password" placeholder="{{__('adminPanel.password')}}">
                        <span toggle="#password" class="fa fa-fw fa-eye field-icon toggle-password"></span>
					</div>
					<div class="form-group">
						<div class="utility">
							<div class="animated-checkbox">
								{{-- <label>
								<input type="checkbox"><span class="label-text">Stay Signed in</span>
								</label> --}}
								 <ul class="app-nav">
         
										<li class="dropdown"><a class="app-nav__item" data-toggle="dropdown" aria-label="Open Profile Menu" style="color: #009688;" ><i class="fa fa-language fa-lg icon-cog"></i> {{__('adminPanel.language')}}</a>
											<ul class="dropdown-menu settings-menu dropdown-menu-right">
												<li><a class="dropdown-item" href="{{ route('set.language', 'en') }}"><i class="flag-icon flag-icon-us"></i> English</a></li>
												<li><a class="dropdown-item" href="{{ route('set.language', 'zh') }}"><i class="flag-icon flag-icon-cn"></i> 中文</a></li>
												<li><a class="dropdown-item" href="{{ route('set.language', 'th') }}"><i class="flag-icon flag-icon-th"></i> ไทย</a></li>
												<li><a class="dropdown-item" href="{{ route('set.language', 'la') }}"><i class="flag-icon flag-icon-la"></i> ລາວ</a></li>
											</ul>
										</li>
									</ul>

							</div>
							<a class="semibold-text mb-2"><a href="#" data-toggle="flip">{{__('adminPanel.forgotPassword')}}</a>
						</div>
					</div>
					<div class="form-group btn-container">
						<button class="btn btn-primary btn-block"><i class="fa fa-sign-in fa-lg fa-fw"></i>{{__('adminPanel.signin')}}</button>
					</div>
				</form>
        <form class="forget-form" method="POST" action="{!! route('vForgetPassword') !!}">
            {{csrf_field()}}
                <h3 class="login-head"><i class="fa fa-lg fa-fw fa-lock"></i>{{__('adminPanel.forgotPassword')}}</h3>
                <div class="form-group">
                    <label class="control-label">{{__('adminPanel.Email')}}</label>
                    <input class="form-control" name="email" id="email" class="email" type="text" placeholder="{{__('adminPanel.Email')}}" autofocus>
                </div>
                <div class="form-group btn-container">
                    <button class="btn btn-primary btn-block reset-password"><i class="fa fa-unlock fa-lg fa-fw"></i>{{__('adminPanel.reset')}}</button>
                </div>
                <div class="form-group mt-3">
                    <p class="semibold-text mb-0"><a href="#" data-toggle="flip"><i class="fa fa-angle-left fa-fw"></i> {{__('adminPanel.backToLogin')}}</a></p>
                </div>
        </form>
			</div>
		</section>
        <!-- Essential javascripts for application to work-->
        <script src="{{ asset('adminPanel/js/jquery-3.3.1.min.js')}}"></script>
        <script src="{{ asset('adminPanel/js/popper.min.js')}}"></script>
        <script src="{{ asset('adminPanel/js/bootstrap.min.js')}}"></script>
        <script src="{{ asset('adminPanel/js/main.js')}}"></script>
		<!-- The javascript plugin to display page loading on top-->
		<script src="{{ asset('adminPanel/js/plugins/pace.min.js')}}"></script>
        <script type="text/javascript">
        $(document).ready(function() {
			// Login Page Flipbox control
			$('.login-content [data-toggle="flip"]').click(function() {
				$('.login-box').toggleClass('flipped');
				return false;
            });

            $('.reset-password').click(function(e) {
                $(".errorField").remove();
                if(!$('#email').val()) {
                    $('#email').after('<span class="errorField">Email Address cannot be empty</span>');
                    e.preventDefault();
                } else {
                    var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
                    if(!emailReg.test($('#email').val())) {
                        $('#email').after('<span class="errorField">Please enter the valid email address</span>');
                        e.preventDefault();
                    }
                }
            });

            $(".toggle-password").click(function() {
                $(this).toggleClass("fa-eye fa-eye-slash");
                var input = $($(this).attr("toggle"));
                if (input.attr("type") == "password") {
                    input.attr("type", "text");
                } else {
                    input.attr("type", "password");
                }
            });
        });
		</script>
	</body>
</html>
