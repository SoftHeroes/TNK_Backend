@extends('adminPanel.layout.app') @section('content')
<!-- Bet History content start -->
<main class="app-content">
    <div class="app-title">
        <div>
            <h1><i class="app-menu__icon fa fa-th-list"></i> {{__('adminPanel.providerList')}}</h1>
            <p>{{__('adminPanel.dashboardDesc')}}</p>
        </div>
        <ul class="app-breadcrumb breadcrumb side">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item active">{{__('adminPanel.provider')}}</li>
            <li class="breadcrumb-item active"><a href="{!!route('vProviderList')!!}">{{__('adminPanel.providerList')}}</a></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="tile">
                <div class="tile-body">
                    <div class="table-responsive">
                        @if($isAllowAll == 'true' || (($isAllowAll == 'false') && (($accessibility == 1) || ($accessibility == 2))))
                            <!-- Button trigger modal -->
                            <div class="form-group col-md-4">
                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addProviderListModal">
                                {{__('adminPanel.newProviderList')}} <i class="fa fa-plus"></i>
                            </button>
                            </div>
                        @endif

                        {{-- modal addProviderListModal --}}

                        <div class="modal fade bd-12-modal-lg" id="addProviderListModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLongTitle">{{__('adminPanel.newProviderList')}}</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    </div>
                                    <div class="modal-body">
                                        <span class="text-danger">* {{__('adminPanel.requiredIndication')}}</span><br><br>
                                        <form action="{{route('vAddProviderList')}}" class="addProviderList" method="POST" >
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
                                                        <label class="control-label">{{__('adminPanel.currency')}} <span class="text-danger">*</span></label>
                                                        <div class="form-group">
                                                            <div class="form-group-select">
                                                                <select class="form-control" name="currencyID">
                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.currency')}}</option>
                                                                    @isset($currencyData)
                                                                        @foreach ($currencyData as $key => $currency)
                                                                            <option value="{{$currency->PID}}">{{$currency->name}} - {{$currency->symbol}} - {{$currency->rate}} </option>
                                                                        @endforeach
                                                                    @endisset
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="control-label">{{__('adminPanel.creditBalance')}} <span class="text-danger">*</span></label>
                                                        <div class="form-group">
                                                            <input class="form-control" name="creditBalance" type="text" placeholder="{{__('adminPanel.creditBalance')}}">
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="control-label">{{__('adminPanel.mainBalance')}} <span class="text-danger">*</span></label>
                                                        <div class="form-group">
                                                            <input class="form-control" name="mainBalance" type="text" placeholder="{{__('adminPanel.mainBalance')}}">
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="control-label">{{__('adminPanel.serverName')}}</label>
                                                        <div class="form-group">
                                                            <input class="form-control" name="serverName" type="text" placeholder="{{__('adminPanel.serverName')}}">
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="control-label">{{__('adminPanel.lastIP')}}</label>
                                                        <div class="form-group">
                                                            <input class="form-control" name="ipList" id="ipaddress" type="text" placeholder="xxx.xxx.xxx.xxx">
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="control-label">{{__('adminPanel.APIKey')}}</label>
                                                        <div class="form-group">
                                                            <input class="form-control" name="APIKey" type="text" placeholder="{{__('adminPanel.APIKey')}}">
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
                                                <button class="btn btn-primary" type="submit" id="btnSubmit">{{__('adminPanel.save')}}</button>
                                                <button class="btn btn-danger" type="button" data-dismiss="modal" aria-label="Close">{{__('adminPanel.cancel')}}</button>
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
                                    <th>{{__('adminPanel.currencyName')}}</th>
                                    <th>{{__('adminPanel.rate')}}</th>
                                    <th>{{__('adminPanel.totalUsers')}}</th>
                                    <th>{{__('adminPanel.totalBets')}}</th>
                                    <th>{{__('adminPanel.totalBetAmount')}}</th>
                                    <th>{{__('adminPanel.totalRollingAmount')}}</th>
                                    <th>{{__('adminPanel.creditBalance')}}</th>
                                    <th>{{__('adminPanel.mainBalance')}}</th>
                                    <th>{{__('adminPanel.UUID')}}</th>
                                    <th>{{__('adminPanel.serverName')}}</th>
                                    <th>{{__('adminPanel.ipList')}}</th>
                                    <th>{{__('adminPanel.isActive')}}</th>
                                    <th>{{__('adminPanel.createdAt')}}</th>
                                    <th>{{ __('adminPanel.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @isset($portalProviderData)
                                @foreach ($portalProviderData  as $key => $providerList)
                                @if($providerList['portalProviderID'] != 1)
                                <tr>
                                    <td>{{$providerList['portalProviderName']}}</td>
                                    <td>{{$providerList['currencyName']}}</td>
                                    <td>{{$providerList['rate']}}</td>
                                    <td>{{$providerList['totalUsers']}}</td>
                                    <td>{{$providerList['totalBets']}}</td>
                                    <td>{{$providerList['totalBetAmount']}}</td>
                                    <td>{{$providerList['totalRollingAmount']}}</td>
                                    <td>{{$providerList['creditBalance']}}</td>
                                    <td>{{$providerList['mainBalance']}}</td>
                                    <td>{{$providerList['portalProviderUUID']}}</td>
                                    <td>{{$providerList['server']}}</td>
                                    <td>{{$providerList['ipList']}}</td>
                                    <td>
                                        @if($providerList['isActive'] == "active")
                                            <span class="badge badge-success" style=" padding: 4px; font-size: 13px;">{{__('adminPanel.active')}}</span>
                                        @else
                                            <span class="badge badge-danger" style=" padding: 4px; font-size: 13px;">{{__('adminPanel.inactive')}}</span>
                                        @endif
                                    </td>
                                    <td>{{$providerList['createdAt']}}</td>
                                    <td>
                                        <div class="d-flex">
											<button type="button" class="btn btn-info btn-sm ml-1" data-toggle="modal" data-target="#providerListViewUserModal{{$providerList['portalProviderID']}}">{{__('adminPanel.viewUsers')}}</button>
                                            <a href="/admin/gameHistory?portalProviderID={{ $providerList['portalProviderUUID'] }}" class='btn btn-info btn-sm ml-1'>{{__('adminPanel.viewGames')}}</a>&nbsp

                                            <form action="{{ route('ProviderLogoutAllUser') }}" method="post">
                                                {{csrf_field()}}
                                                <input hidden name="portalProviderPID" value="{{$providerList['portalProviderID']}}">
                                                <button class="btn btn-danger btn-sm"  type="submit">{{__('adminPanel.clearProvider')}}</button>
                                            </form>
                                            @if($isAllowAll == 'true' || (($isAllowAll == 'false') && (($accessibility == 1) || ($accessibility == 2))))
                                                <button type="button" class="btn btn-primary btn-sm ml-1" data-toggle="modal" data-target="#editProviderListModal{{$providerList['portalProviderID']}}"><i class="fa fa-edit"></i></button>
                                            @endif
                                        </div>

                                        {{-- modal editProviderListModal --}}
                                        @if($isAllowAll == 'true' || (($isAllowAll == 'false') && (($accessibility == 1) || ($accessibility == 2))))
                                        <div class="modal fade bd-12-modal-lg" id="editProviderListModal{{$providerList['portalProviderID']}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                            <div class="modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="exampleModalLongTitle">{{__('adminPanel.editProviderList')}}</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <span class="text-danger">* {{__('adminPanel.requiredIndication')}}</span><br><br>
                                                        <form action="{{route('vUpdateProviderList')}}" class="editProviderListModal-form{{$providerList['portalProviderID']}}" method="POST" >
                                                            {{csrf_field()}}
                                                            <input hidden name="providerListID" value="{{$providerList['portalProviderID']}}">
                                                            <div class="row">
                                                                <div class="col-lg-12">

                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.name')}} <span class="text-danger">*</span></label>
                                                                        <div class="form-group">
                                                                            <input class="form-control" name="name" type="text" placeholder="{{__('adminPanel.name')}}" value="{{ $providerList['portalProviderName'] }}">
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.currency')}} <span class="text-danger">*</span></label>
                                                                        <div class="form-group">
                                                                            <div class="form-group-select">
                                                                                <select class="form-control" name="currencyID">
                                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.currency')}}</option>
                                                                                    @isset($currencyData)
                                                                                        @foreach ($currencyData as $key => $currency)
                                                                                            <option value="{{$currency->PID}}" {{$providerList["currencyID"] == $currency->PID ? 'selected':''}}>{{$currency->name}} - {{$currency->symbol}} - {{$currency->rate}} </option>
                                                                                        @endforeach
                                                                                    @endisset
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
                                                                                    <option value="active" {{$providerList["isActive"] == 'active' ? 'selected':''}}>{{__('adminPanel.active')}}</option>
                                                                                    <option value="inactive" {{$providerList["isActive"] == 'inactive' ? 'selected':''}}>{{__('adminPanel.inactive')}}</option>
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
                                        @endif
                                        <div class="modal fade modal-fullscreen-xl" id="providerListViewUserModal{{$providerList['portalProviderID']}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                            <div class="modal-dialog modal-dialogs" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="exampleModalLongTitle">{{$providerList['portalProviderName']}}</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <table class="table table-hover table-bordered sampleTable{{$providerList['portalProviderID']}}">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{__('adminPanel.userID')}}</th>
                                                                    <th>{{__('adminPanel.username')}}</th>
                                                                    <th>{{__('adminPanel.firstName')}}</th>
                                                                    <th>{{__('adminPanel.lastName')}}</th>
                                                                    <th>{{__('adminPanel.balance')}}</th>
                                                                    <th>{{__('adminPanel.isLoggedIn')}}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($providerList['userDetails']  as $key => $userDetail)
                                                                <tr>
                                                                <td> <a href="{{ route('vUserProfile', ['userUUID' => base64_encode($userDetail->userUUID)]) }}">{{ $userDetail->userUUID }}</a></td>
                                                                <td>{{$userDetail->userName}}</td>
                                                                <td>{{$userDetail->firstName}}</td>
                                                                <td>{{$userDetail->lastName}}</td>
                                                                <td>{{$userDetail->balance}}</td>
                                                                <td><span class="badge {{$userDetail->isLoggedIn == 'true' ? 'badge-success' : 'badge-danger'}}" style=" padding: 4px; font-size: 13px;">{{ $userDetail->isLoggedIn == 'true' ? __('adminPanel.isOnline') : __('adminPanel.isOffline') }}</span></td>
                                                            </tr>
                                                                @endforeach

                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                </div>
                                            </div>
                                        </div>

                                        <script>
											$(document).ready(function() {
											    var providerListID = {{$providerList["portalProviderID"]}};
                                                $('.sampleTable'+providerListID).DataTable();

                                                $(".editProviderListModal-form"+providerListID).validate({
                                                    rules: {
                                                        name: {
                                                            required: true
                                                        },
                                                        currencyID: {
                                                            required: true
                                                        },
                                                        creditBalance: {
                                                            required: true,
                                                            number: true,
                                                            min: 0
                                                        },
                                                        mainBalance: {
                                                            required: true,
                                                            number: true,
                                                            min: 0
                                                        },
                                                        isActive: {
                                                            required: true
                                                        }
                                                    },
                                                    messages: {
                                                        name: {
                                                            required: "Please Enter Name"
                                                        },
                                                        currencyID: {
                                                            required: "Please Select Currency"
                                                        },
                                                        creditBalance: {
                                                            required: "Please Enter Credit Balance"
                                                        },
                                                        mainBalance: {
                                                            required: "Please Enter Main Balance"
                                                        },
                                                        isActive: {
                                                            required: "Please Select Active Status"
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
                                @endif
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
<script type="text/javascript" src="https://rawgit.com/RobinHerbots/jquery.inputmask/3.x/dist/jquery.inputmask.bundle.js"></script>
<script type="text/javascript">  // Input mask bundle ip address
        $('#ipaddress').inputmask({
            alias: "ip",
            greedy: false //The initial mask shown will be "" instead of "-____".
        });
        
	$(document).ready(function() {
        var table = $('#sampleTable').DataTable({
            responsive: true,
            dom: "<'row'<'col-sm-5'l><'col-sm-7'f>>"+"<'row'<'col-sm-12'tr>>"+"<'row'<'col-sm-5'i><'col-sm-7'p>>",
            language:{
                search: "_INPUT_",
                searchPlaceholder: "{{__('adminPanel.search')}}"
            }
        });
        $('.dataTables_filter').prepend('<label>').children().first().append('@lang('adminPanel.searchByFieldName'):<select class="form-control form-control-sm selectable"><option>@lang('adminPanel.all')</option><option value="0">@lang('adminPanel.name')</option><option value="1">@lang('adminPanel.currencyName')</option><option value="2">@lang('adminPanel.rate')</option><option value="7">@lang('adminPanel.creditBalance')</option><option value="8">@lang('adminPanel.mainBalance')</option><option value="9">@lang('adminPanel.UUID')</option><option value="10">@lang('adminPanel.server')</option><option value="11">@lang('adminPanel.ipList')</option><option value="12">@lang('adminPanel.isActive')</option></select>').dataTableFilter(table);
        $('.dataTables_filter').append('<br><button class="btn" onClick="window.location.reload();"><i class="fa fa-refresh" aria-hidden="true"></i>@lang('adminPanel.refresh')</button>').dataTableFilter(table);

      
    });
</script>

<style>
    .modal-dialogs {
        max-width: 65% !important;
        margin: 1.75rem auto;
    }
    em{
        color: red
    }
</style>
<!-- Bet History content end -->
@endsection
