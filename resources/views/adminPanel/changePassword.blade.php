@extends('adminPanel.layout.app')
@section('content')

<main class="app-content">
    <div class="app-title">
        <div>
            <h1><i class="app-menu__icon fa fa-th-list"></i>{{__('adminPanel.changePassword')}}</h1>
            <p>{{__('adminPanel.dashboardDesc')}}</p>
        </div>
        <ul class="app-breadcrumb breadcrumb side">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item active"><a href="{!!route('vChangePassword')!!}">{{__('adminPanel.changePassword')}}</a></li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
           <div class="card-body">
                    <form method="POST" action="{{ route('changePassword') }}">
                        @csrf
                         @foreach ($errors->all() as $error)
                            {{-- <p class="text-danger">{{ $error }}</p> --}}
                         @endforeach
                           @if( isset($message) )
                                <div class="form-group row">
                                <div class="col-md-3"></div>
                            <div class="col-md-7">
                               <div class="alert alert-success" role="alert">
                                {{$message}}
                                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                </div>
                            </div>
                        </div>
                         @endif

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{__('adminPanel.currentPassword')}}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control" name="current_password" autocomplete="current-password">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{__('adminPanel.newPassword')}}</label>

                            <div class="col-md-6">
                                <input id="new_password" type="password" class="form-control" name="new_password" autocomplete="current-password">
                            </div>

                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{__('adminPanel.newConfirmPassword')}}</label>

                            <div class="col-md-6">
                                <input id="new_confirm_password" type="password" class="form-control" name="new_confirm_password" autocomplete="current-password">
                          <br>
                            <span id="divCheckPasswordMatch"></span>
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-outline-primary" id="btnSubmit">
                                    {{__('adminPanel.updatePassword')}}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
$(document).ready(function(){
 $("#new_confirm_password, #new_password, #password").keyup(function() {
        var password = $("#new_password").val();
        var confirmPassword = $("#new_confirm_password").val();
        var oldPassword = $("#password").val();
        if(password != "" && confirmPassword != "" && oldPassword != ""){
            if(password == confirmPassword){
                $("#divCheckPasswordMatch").html( "Passwords match.").css('color', 'green');
                $("#btnSubmit").attr("disabled", false);
           }else{
                $("#divCheckPasswordMatch").html( "Passwords do not match!").css('color', 'red');
                $('#btnSubmit').attr("disabled", true);
            }
        }else{
            $("#divCheckPasswordMatch").html("");
            $('#btnSubmit').attr("disabled", true);
        }

        if (oldPassword == "") {
            $("#divCheckPasswordMatch").html( "Please enter the old password").css('color', 'red');
            $('#btnSubmit').attr("disabled", true);
        }

        if (password.length < 8) {
            $("#divCheckPasswordMatch").html( "Password should be atleast 8 characters long!").css('color', 'red');
            $('#btnSubmit').attr("disabled", true);
        }
        if (password != confirmPassword) {
            $("#divCheckPasswordMatch").html("Passwords does not match").css('color', 'red');
            $('#btnSubmit').attr("disabled", true);
        }
    });

    $('#btnSubmit').attr("disabled", true);
});
</script>
@endsection
