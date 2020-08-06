@extends('adminPanel.layout.app') @section('content')
<!-- Bet History content start -->
<main class="app-content">
    <div class="app-title">
        <div>
            <h1><i class="app-menu__icon fa fa-th-list"></i> {{__('adminPanel.adminPolicy')}}</h1>
            <p>{{__('adminPanel.dashboardDesc')}}</p>
        </div>
        <ul class="app-breadcrumb breadcrumb side">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item active"><a href="{!!route('vAdminPolicy')!!}">{{__('adminPanel.adminPolicy')}}</a></li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="tile">
                <div class="tile-body">
                    <div class="table-responsive">
                        <!-- Button trigger modal -->
                        @if($isAllowAll == 'true' || (($isAllowAll == 'false') && (($accessibility == 1) || ($accessibility == 2))))
                            <div class="form-group col-md-4">
                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addAdminpolicyModal">
                                {{__('adminPanel.newAdminPolicy')}} <i class="fa fa-plus"></i>
                            </button>
                            </div>
                        @endif

                        <!-- Modal -->
                        <div class="modal fade bd-12-modal-lg" id="addAdminpolicyModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLongTitle">{{__('adminPanel.newAdminPolicy')}}</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <span class="text-danger">* {{__('adminPanel.requiredIndication')}}</span><br><br>
                                        <form action="{{route('AddAdminPolicy')}}" class="AddNewAdminPolicy" method="post">
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
                                                        <label class="control-label">{{__('adminPanel.userLockTime')}}</label>
                                                        <div class="form-group">
                                                            <input class="form-control" name="userLockTime" type="number" placeholder="{{__('adminPanel.userLockTime')}}">
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="control-label">{{__('adminPanel.invalidAttemptsAllowed')}}</label>
                                                        <div class="form-group">
                                                            <input class="form-control" name="invalidAttemptsAllowed" type="number" placeholder="{{__('adminPanel.invalidAttemptsAllowed')}}">
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="control-label">{{__('adminPanel.otpValidTimeInSeconds')}} <span class="text-danger">*</span></label>
                                                        <div class="form-group">
                                                            <input class="form-control" name="otpValidTimeInSeconds" type="number" placeholder="{{__('adminPanel.otpValidTimeInSeconds')}}">
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="control-label">{{__('adminPanel.passwordResetTime')}}</label>
                                                        <div class="form-group">
                                                            <input class="form-control" name="passwordResetTime" type="number" placeholder="{{__('adminPanel.passwordResetTime')}}">
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="control-label">{{__('adminPanel.access')}} <span class="text-danger">*</span></label>
                                                        <div class="form-group">
                                                            <div class="form-group-select">
                                                                <select class="form-control" name="access">
                                                                <option value="">{{__('adminPanel.select')}} {{__('adminPanel.access')}}</option>
                                                                <option value="1">All</option>
                                                                <option value="2">appAPI</option>
                                                                <option value="3">AdminPanel</option>
                                                                <option value="4">webApi</option>
                                                                <option value="5">exposeApi</option>
                                                            </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="control-label">{{__('adminPanel.source')}} <span class="text-danger">*</span></label>
                                                        <div class="form-group">
                                                            <div class="form-group-select">
                                                                <select class="form-control" name="source">
                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.source')}}</option>
                                                                    <option value="1">All</option>
                                                                    <option value="2">web</option>
                                                                    <option value="3">ios</option>
                                                                    <option value="4">android</option>
                                                                </select>
                                                            </div>
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
                                                <button class="btn btn-primary" type="submit">{{__('adminPanel.create')}}</button>
                                                <button class="btn btn-danger" type="reset" data-dismiss="modal" aria-label="Close">{{__('adminPanel.cancel')}}</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <table class="table table-hover table-bordered" id="sampleTable">
                            <thead>
                                <tr>
                                    <th>{{__('adminPanel.name')}}</th>
                                    <th>{{__('adminPanel.userLockTime')}}</th>
                                    <th>{{__('adminPanel.invalidAttemptsAllowed')}}</th>
                                    <th>{{__('adminPanel.otpValidTimeInSeconds')}}</th>
                                    <th>{{__('adminPanel.passwordResetTime')}}</th>
                                    <th>{{__('adminPanel.access')}}</th>
                                    <th>{{__('adminPanel.source')}}</th>
                                    <th>{{__('adminPanel.isActive')}}</th>
                                    <th>{{__('adminPanel.createdAt')}}</th>
                                    <th>{{__('adminPanel.deletedAt')}}</th>
                                    <th>{{ __('adminPanel.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @isset($adminPolicyData) @foreach ($adminPolicyData as $key => $adminPolicy) @if($adminPolicy->PID != 1)
                                <tr @php if ($adminPolicy->deletedAt != "Null") { echo "style=background-color:#f37575";} @endphp>
                                    <td>{{$adminPolicy->name}}</td>
                                    <td>{{$adminPolicy->userLockTime}}</td>
                                    <td>{{$adminPolicy->invalidAttemptsAllowed}}</td>
                                    <td>{{$adminPolicy->otpValidTimeInSeconds}}</td>
                                    <td>{{$adminPolicy->passwordResetTime}}</td>
                                    <td>{{$adminPolicy->access_name}}</td>
                                    <td>{{$adminPolicy->source_name}}</td>
                                    <td>
                                        @if($adminPolicy->isActive == 'active')
                                        <span class="badge badge-success" style=" padding: 4px; font-size: 13px;">{{__('adminPanel.active')}}</span>
                                        @else
                                            <span class="badge badge-danger" style=" padding: 4px; font-size: 13px;">{{__('adminPanel.inactive')}}</span>
                                        @endif
                                    </td>
                                    <td>{{$adminPolicy->createdAt}}</td>
                                    <td> 
                                        @if($adminPolicy->deletedAt != 'Null')
                                            {{$adminPolicy->deletedAt}}
                                        @else
                                         {{__('adminPanel.null')}}
                                        @endif
                                        </td>
                                    <td>
                                        <div class="d-flex">
                                            @if($adminPolicy->deletedAt == 'Null')
                                                @if($isAllowAll == 'true' || (($isAllowAll == 'false') && ($accessibility == 2)))
                                                    <form action="{{ route('DeleteAdminPolicy') }}" method="post">
                                                        {{csrf_field()}}
                                                        <input hidden name="adminPolicyPID" value="{{$adminPolicy->PID}}">
                                                        <button class="btn btn-danger btn-sm" onclick="return confirm('@lang('adminPanel.msgDelete')')" type="submit"> <i class="fa fa-trash"></i></button>
                                                    </form>
                                                @endif
                                                @if($isAllowAll == 'true' || (($isAllowAll == 'false') && (($accessibility == 1) || ($accessibility == 2))))
                                                    <button type="button" class="btn btn-primary btn-sm ml-1" data-toggle="modal" data-target="#addAdminpolicyModal{{$adminPolicy->PID}}"><i class="fa fa-edit"></i></button>
                                                @endif
                                            @else
                                                <form action="{{ route('RestoreAdminPolicy') }}" method="post">
                                                    {{csrf_field()}}
                                                    <input hidden name="adminPolicyID" value="{{$adminPolicy->PID}}">
                                                    <button class="btn btn-success btn-sm" onclick="return confirm('Are you sure to restore?')" type="submit"> <i class="fa fa-refresh">{{__('adminPanel.restore')}}</i></button>
                                                </form>
                                            @endif
                                        </div>

                                        <!-- Modal -->
                                        <div class="modal fade bd-12-modal-lg" id="addAdminpolicyModal{{$adminPolicy->PID}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                            <div class="modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="exampleModalLongTitle">{{__('adminPanel.editAdminPolicy')}}</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="modal-body">
                                                    <span class="text-danger">* {{__('adminPanel.requiredIndication')}}</span><br><br>
                                                        <form action="{{route('UpdateAdminPolicy')}}" class="UpdateNewAdminPolicy{{$adminPolicy->PID}}" method="post">
                                                            {{csrf_field()}}
                                                            <input hidden name="adminPolicyPID" value="{{$adminPolicy->PID}}">
                                                            <div class="row">
                                                                <div class="col-lg-12">
                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.name')}} <span class="text-danger">*</span></label>
                                                                        <div class="form-group">
                                                                            <input class="form-control" name="name" type="text" placeholder="{{__('adminPanel.name')}}" value="{{$adminPolicy->name}}">
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.userLockTime')}}</label>
                                                                        <div class="form-group">
                                                                            <input class="form-control" name="userLockTime" type="number" placeholder="{{__('adminPanel.userLockTime')}}" value="{{$adminPolicy->userLockTime}}">
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.invalidAttemptsAllowed')}}</label>
                                                                        <div class="form-group">
                                                                            <input class="form-control" name="invalidAttemptsAllowed" type="number" placeholder="{{__('adminPanel.invalidAttemptsAllowed')}}" value="{{$adminPolicy->invalidAttemptsAllowed}}">
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.otpValidTimeInSeconds')}} <span class="text-danger">*</span></label>
                                                                        <div class="form-group">
                                                                            <input class="form-control" name="otpValidTimeInSeconds" type="number" placeholder="{{__('adminPanel.otpValidTimeInSeconds')}}" value="{{$adminPolicy->otpValidTimeInSeconds}}">
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.passwordResetTime')}}</label>
                                                                        <div class="form-group">
                                                                            <input class="form-control" name="passwordResetTime" type="number" placeholder="{{__('adminPanel.passwordResetTime')}}" value="{{$adminPolicy->passwordResetTime}}">
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.access')}} <span class="text-danger">*</span></label>
                                                                        <div class="form-group">
                                                                            <div class="form-group-select">
                                                                                <select class="form-control" name="access">
                                                                                <option value="">{{__('adminPanel.select')}} {{__('adminPanel.access')}}</option>
                                                                                <option value="1" {{$adminPolicy->access == 1 ? 'selected':''}}>All</option>
                                                                                <option value="2" {{$adminPolicy->access == 2 ? 'selected':''}}>appAPI</option>
                                                                                <option value="3" {{$adminPolicy->access == 3 ? 'selected':''}}>AdminPanel</option>
                                                                                <option value="4" {{$adminPolicy->access == 4 ? 'selected':''}}>webApi</option>
                                                                                <option value="5" {{$adminPolicy->access == 5 ? 'selected':''}}>exposeApi</option>
                                                                            </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.source')}} <span class="text-danger">*</span></label>
                                                                        <div class="form-group">
                                                                            <div class="form-group-select">
                                                                                <select class="form-control" name="source">
                                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.source')}}</option>
                                                                                    <option value="1" {{$adminPolicy->source == 1 ? 'selected':''}}>All</option>
                                                                                    <option value="2" {{$adminPolicy->source == 2 ? 'selected':''}}>web</option>
                                                                                    <option value="3" {{$adminPolicy->source == 3 ? 'selected':''}}>ios</option>
                                                                                    <option value="4" {{$adminPolicy->source == 4 ? 'selected':''}}>android</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.isActive')}} <span class="text-danger">*</span></label>
                                                                        <div class="form-group">
                                                                            <div class="form-group-select">
                                                                                <select class="form-control" name="isActive">
                                                                                    <option value="">{{__('adminPanel.selectActiveStatus')}}</option>
                                                                                    <option value="active" {{$adminPolicy->isActive == 'active' ? 'selected':''}}>{{__('adminPanel.active')}}</option>
                                                                                    <option value="inactive" {{$adminPolicy->isActive == 'inactive' ? 'selected':''}}>{{__('adminPanel.inactive')}}</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                            </div>
                                                            <div class="col-lg-12">
                                                                <button class="btn btn-primary" type="submit">{{__('adminPanel.save')}}</button>
                                                                <button class="btn btn-danger" type="button" data-dismiss="modal" aria-label="Close">{{__('adminPanel.cancel')}}</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <script>
                                            $(document).ready(function(){
                                                var PID = {{$adminPolicy->PID}};
                                              $(".UpdateNewAdminPolicy"+PID).validate({
                                                rules: {
                                                    name: {
                                                        required: true
                                                    },
                                                    otpValidTimeInSeconds: {
                                                        required: true,
                                                        number: true,
                                                        min: 0
                                                    },
                                                    access: {
                                                        required: true
                                                    },
                                                    source: {
                                                        required: true
                                                    },
                                                    isActive: {
                                                        required: true
                                                    }
                                                },
                                                messages: {
                                                    name: {
                                                        required: "Please enter name"
                                                    },
                                                    otpValidTimeInSeconds: {
                                                        required: "Please enter OTP Valid TimeIn Seconds"
                                                    },
                                                    access: {
                                                        required: "Please Select access"
                                                    },
                                                    source: {
                                                        required: "Please Select source"
                                                    },
                                                    isActive: {
                                                        required: "Please select Active Status"
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
                                @endif @endforeach @endisset
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
        $('.dataTables_filter').prepend('<label>').children().first().append('@lang('adminPanel.searchByFieldName'):<select class="form-control form-control-sm selectable"><option>@lang('adminPanel.all')</option><option value="0">@lang('adminPanel.name')</option><option value="5">@lang('adminPanel.access')</option><option value="6">@lang('adminPanel.source')</option><option value="7">@lang('adminPanel.isActive')</option></select>').dataTableFilter(table);
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
<style>
    em{
        color: red;
    }
</style>
<!-- Bet History content end -->
@endsection
