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
	</head>
	<body>
        <!-- Essential javascripts for application to work-->
		<script src="{{ asset('adminPanel/js/jquery-3.3.1.min.js')}}"></script>
		<script src="{{ asset('adminPanel/js/popper.min.js')}}"></script>
        <script src="{{ asset('adminPanel/js/bootstrap.min.js')}}"></script>
        <script src="{{ asset('adminPanel/js/main.js')}}"></script>
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
					        type: "warning"
					    });
				</script>
				@endforeach
			</div>
			@endif
			{{-- Success Message Code --}}
			@if(session()->has('message'))
			<div class="alert alert-success">
				{{ session()->get('message') }}
			</div>
            @endif

            @if(isset($otpVerified) && ($otpVerified == "false"))
                <div class="login-box otp">
                    @if(isset($errorMessage))
                        <div class="error">{{$errorMessage}}</div>
                    @else
                        <div class="error"></div>
                    @endif
                    <form class="login-form otp-enter" method="POST" action="{!! route('vOtpCheck') !!}">
                        {{csrf_field()}}
                        <h3 class="login-head"><i class="fa fa-lg fa-fw fa-user"></i>{{__('adminPanel.resetPassword')}}</h3>
                        <div class="form-group">
                            <label class="control-label">{{__('adminPanel.enterTheOtp')}}</label>
                            <input class="form-control" name="otp" id="otp" type="text" placeholder="{{__('adminPanel.enterTheOtp')}}" autofocus>
                        </div>
                        <div class="form-group btn-container">
                            <button class="btn btn-primary btn-block send-otp"><i class="fa fa-sign-in fa-lg fa-fw"></i>{{__('adminPanel.submit')}}</button>
                        </div>
                        <div class="form-group mt-3">
                            <p class="semibold-text mb-0"><a href="{!!route('vLogin')!!}" data-toggle="flip"><i class="fa fa-angle-left fa-fw"></i> {{__('adminPanel.backToLogin')}}</a></p>
                        <div class="form-group">
                    </form>
                </div>
            @endif

            @if(isset($otpVerified) && ($otpVerified == "true"))
                <div class="login-box password-reset">
                    @if(isset($errorMessage))
                        <div class="error">{{$errorMessage}}</div>
                    @else
                        <div class="error"></div>
                    @endif
                    <form class="login-form password-reset" method="POST" action="{!! route('vResetPassword') !!}">
                        {{csrf_field()}}
                        <h3 class="login-head"><i class="fa fa-lg fa-fw fa-user"></i>{{__('adminPanel.resetPassword')}}</h3>
                        <div class="form-group">
                            <label class="control-label">{{__('adminPanel.Email')}}</label>
                            <input class="form-control" id="email" name="email" value={{$passwordResetEmailID}} type="text" placeholder="{{__('adminPanel.Email')}}" autofocus readonly>
                        </div>
                        <div class="form-group">
                            <label class="control-label">{{__('adminPanel.newPassword')}}</label>
                            <input class="form-control" id="new_password" name="new_password" type="password" placeholder="{{__('adminPanel.newPassword')}}">
                            <span toggle="#new_password" class="fa fa-fw fa-eye field-icon toggle-password"></span>
                        </div>
                        <div class="form-group">
                            <label class="control-label">{{__('adminPanel.confirmPassword')}}</label>
                            <input class="form-control" id="confirm_password" name="confirm_password" type="password" placeholder="{{__('adminPanel.confirmPassword')}}">
                            <span toggle="#confirm_password" class="fa fa-fw fa-eye field-icon toggle-password"></span>
                        </div>
                        <div class="form-group btn-container">
                            <button class="btn btn-primary btn-block reset-password"><i class="fa fa-sign-in fa-lg fa-fw"></i>{{__('adminPanel.submit')}}</button>
                        </div>
                        <div class="form-group mt-3">
                            <p class="semibold-text mb-0"><a href="{!!route('vLogin')!!}" data-toggle="flip"><i class="fa fa-angle-left fa-fw"></i> {{__('adminPanel.backToLogin')}}</a></p>
                        <div class="form-group">
                    </form>
                </div>
            @endif
		</section>

		<!-- The javascript plugin to display page loading on top-->
        <script src="{{ asset('adminPanel/js/plugins/pace.min.js')}}"></script>
        <script type="text/javascript">
        $(document).ready(function() {

			$otpValue = $('#otp').val();
            $('.send-otp').click(function(e) {
                $(".errorField").remove();
                if(!$('#otp').val()) {
                    $('#otp').after('<span class="errorField">OTP cannot be empty !!</span>');
                    e.preventDefault();
                } else {
                    if(($('#otp').val().length != 4)) {
                        $('#otp').after('<span class="errorField">Enter the valid OTP !!</span>');
                        e.preventDefault();
                    }
                }
            });

            $('.reset-password').click(function(e) {
                var email = $('#email').val();
                var newPassword = $('#new_password').val();
                var confirmPassword = $('#confirm_password').val();
                $(".errorField").remove();

                if (email.length < 1) {
                    $('#email').after('<span class="errorField">This field is required</span>');
                    e.preventDefault();
                }
                else {
                    var regEx = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
                    var validEmail = regEx.test(email);
                    if (!validEmail) {
                        $('#email').after('<span class="errorField">Enter a valid email</span>');
                        e.preventDefault();
                    }
                }
                if (newPassword.length < 8) {
                    $('#new_password').after('<span class="errorField">Password must be at least 8 characters</span>');
                    e.preventDefault();
                }
                if (newPassword != confirmPassword) {
                    $('#confirm_password').after('<span class="errorField">Passwords does not match</span>');
                    e.preventDefault();
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
