@extends('adminPanel.layout.app') @section('content')
<!-- Bet History content start -->
<main class="app-content">
    <div class="app-title">
        <div>
            <h1><i class="app-menu__icon fa fa-th-list"></i> {{__('adminPanel.accessPolicy')}}</h1>
            <p>{{__('adminPanel.dashboardDesc')}}</p>
        </div>
        <ul class="app-breadcrumb breadcrumb side">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item active"><a href="{!!route('vAccessPolicy')!!}">{{__('adminPanel.accessPolicy')}}</a></li>
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
                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addAccessPolicyModal">
                                    {{__('adminPanel.newAccessPolicy')}} <i class="fa fa-plus"></i>
                                </button>
                            </div>
                        @endif

                        <!-- Modal -->
                        <div class="modal fade bd-12-modal-lg" id="addAccessPolicyModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLongTitle">{{__('adminPanel.newAccessPolicy')}}</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <span class="text-danger">* {{__('adminPanel.requiredIndication')}}</span><br><br>
                                        <form action="{{route('vCreateAccessPolicy')}}" class="createAccessPolicy" method="post">
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
                                                        <label class="control-label">{{__('adminPanel.isAllowAll')}} <span class="text-danger">*</span></label>
                                                        <div class="form-group">
                                                            <div class="form-group-select">
                                                                <select class="form-control" name="isAllowAll">
                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.isAllowAll')}} </option>
                                                                    <option value="true">{{__('adminPanel.allow')}}</option>
                                                                    <option value="false">{{__('adminPanel.disallow')}}</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="control-label">{{__('adminPanel.selectPortalProvider')}}</label>
                                                        <div class="form-group">
                                                            <div class="form-group-select">
                                                                <select class="form-control" id="demoSelect" multiple="" name="portalProviderID[]">
                                                                    <optgroup label="{{__('adminPanel.selectPortalProvider')}}">
                                                                        @foreach ($portalProviderData as $key => $portalProvider)
                                                                        @if($portalProvider->PID !=1)
                                                                            <option value="{{$portalProvider->PID}}">{{$portalProvider->name}}</option>
                                                                        @endif
                                                                        @endforeach
                                                                    </optgroup>
                                                                  </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="control-label">{{__('adminPanel.accessAdminPolicy')}} <span class="text-danger">*</span></label>
                                                        <div class="form-group">
                                                            <div class="form-group-select">
                                                                <select class="form-control" name="accessAdminPolicy">
                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.accessAdminPolicy')}}</option>
                                                                    <option value="2">{{__('adminPanel.read/write/delete')}}</option>
                                                                    <option value="1">{{__('adminPanel.read/write')}}</option>
                                                                    <option value="0">{{__('adminPanel.read')}}</option>
                                                                    <option value="3">{{__('adminPanel.hide')}}</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="control-label">{{__('adminPanel.accessAccessPolicy')}} <span class="text-danger">*</span></label>
                                                        <div class="form-group">
                                                            <div class="form-group-select">
                                                                <select class="form-control" name="accessAccessPolicy">
                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.accessAccessPolicy')}}</option>
                                                                    <option value="2">{{__('adminPanel.read/write/delete')}}</option>
                                                                    <option value="1">{{__('adminPanel.read/write')}}</option>
                                                                    <option value="0">{{__('adminPanel.read')}}</option>
                                                                    <option value="3">{{__('adminPanel.hide')}}</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label class="control-label">{{__('adminPanel.accessAdminInformation')}} <span class="text-danger">*</span></label>
                                                        <div class="form-group">
                                                            <div class="form-group-select">
                                                                <select class="form-control" name="accessAdminInformation">
                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.accessAdminInformation')}}</option>
                                                                    <option value="2">{{__('adminPanel.read/write/delete')}}</option>
                                                                    <option value="1">{{__('adminPanel.read/write')}}</option>
                                                                    <option value="0">{{__('adminPanel.read')}}</option>
                                                                    <option value="3">{{__('adminPanel.hide')}}</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="control-label">{{__('adminPanel.accessProviderList')}} <span class="text-danger">*</span></label>
                                                        <div class="form-group">
                                                            <div class="form-group-select">
                                                                <select class="form-control" name="accessProviderList">
                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.accessProviderList')}}</option>
                                                                    <option value="2">{{__('adminPanel.read/write/delete')}}</option>
                                                                    <option value="1">{{__('adminPanel.read/write')}}</option>
                                                                    <option value="0">{{__('adminPanel.read')}}</option>
                                                                    <option value="3">{{__('adminPanel.hide')}}</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                               
                                                    <div class="form-group">
                                                        <label class="control-label">{{__('adminPanel.accessProviderConfig')}} <span class="text-danger">*</span></label>
                                                        <div class="form-group">
                                                            <div class="form-group-select">
                                                                <select class="form-control" name="accessProviderConfig">
                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.accessProviderConfig')}}</option>
                                                                    <option value="2">{{__('adminPanel.read/write/delete')}}</option>
                                                                    <option value="1">{{__('adminPanel.read/write')}}</option>
                                                                    <option value="0">{{__('adminPanel.read')}}</option>
                                                                    <option value="3">{{__('adminPanel.hide')}}</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label class="control-label">{{__('adminPanel.accessCurrency')}} <span class="text-danger">*</span></label>
                                                        <div class="form-group">
                                                            <div class="form-group-select">
                                                                <select class="form-control" name="accessCurrency">
                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.accessCurrency')}}</option>
                                                                    <option value="2">{{__('adminPanel.read/write/delete')}}</option>
                                                                    <option value="1">{{__('adminPanel.read/write')}}</option>
                                                                    <option value="0">{{__('adminPanel.read')}}</option>
                                                                    <option value="3">{{__('adminPanel.hide')}}</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label class="control-label">{{__('adminPanel.accessBetRule')}} <span class="text-danger">*</span></label>
                                                        <div class="form-group">
                                                            <div class="form-group-select">
                                                                <select class="form-control" name="accessBetRule">
                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.accessBetRule')}}</option>
                                                                    <option value="2">{{__('adminPanel.read/write/delete')}}</option>
                                                                    <option value="1">{{__('adminPanel.read/write')}}</option>
                                                                    <option value="0">{{__('adminPanel.read')}}</option>
                                                                    <option value="3">{{__('adminPanel.hide')}}</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label class="control-label">{{__('adminPanel.accessBetSetup')}} <span class="text-danger">*</span></label>
                                                        <div class="form-group">
                                                            <div class="form-group-select">
                                                                <select class="form-control" name="accessBetSetup">
                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.accessBetSetup')}}</option>
                                                                    <option value="2">{{__('adminPanel.read/write/delete')}}</option>
                                                                    <option value="1">{{__('adminPanel.read/write')}}</option>
                                                                    <option value="0">{{__('adminPanel.read')}}</option>
                                                                    <option value="3">{{__('adminPanel.hide')}}</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="control-label">{{__('adminPanel.accessInvitationSetup')}} <span class="text-danger">*</span></label>
                                                        <div class="form-group">
                                                            <div class="form-group-select">
                                                                <select class="form-control" name="accessInvitationSetup">
                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.accessInvitationSetup')}}</option>
                                                                    <option value="2">{{__('adminPanel.read/write/delete')}}</option>
                                                                    <option value="1">{{__('adminPanel.read/write')}}</option>
                                                                    <option value="0">{{__('adminPanel.read')}}</option>
                                                                    <option value="3">{{__('adminPanel.hide')}}</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="control-label">{{__('adminPanel.accessProviderGameSetup')}} <span class="text-danger">*</span></label>
                                                        <div class="form-group">
                                                            <div class="form-group-select">
                                                                <select class="form-control" name="accessProviderGameSetup">
                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.accessProviderGameSetup')}}</option>
                                                                    <option value="2">{{__('adminPanel.read/write/delete')}}</option>
                                                                    <option value="1">{{__('adminPanel.read/write')}}</option>
                                                                    <option value="0">{{__('adminPanel.read')}}</option>
                                                                    <option value="3">{{__('adminPanel.hide')}}</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="control-label">{{__('adminPanel.accessProviderRequestList')}} <span class="text-danger">*</span></label>
                                                        <div class="form-group">
                                                            <div class="form-group-select">
                                                                <select class="form-control" name="accessProviderRequestList">
                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.accessProviderRequestList')}}</option>
                                                                    <option value="2">{{__('adminPanel.read/write/delete')}}</option>
                                                                    <option value="1">{{__('adminPanel.read/write')}}</option>
                                                                    <option value="0">{{__('adminPanel.read')}}</option>
                                                                    <option value="3">{{__('adminPanel.hide')}}</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="control-label">{{__('adminPanel.accessProviderRequestBalance')}} <span class="text-danger">*</span></label>
                                                        <div class="form-group">
                                                            <div class="form-group-select">
                                                                <select class="form-control" name="accessProviderRequestBalance">
                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.accessProviderRequestBalance')}}</option>
                                                                    <option value="2">{{__('adminPanel.read/write/delete')}}</option>
                                                                    <option value="1">{{__('adminPanel.read/write')}}</option>
                                                                    <option value="0">{{__('adminPanel.read')}}</option>
                                                                    <option value="3">{{__('adminPanel.hide')}}</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="control-label">{{__('adminPanel.accessProviderInfo')}} <span class="text-danger">*</span></label>
                                                        <div class="form-group">
                                                            <div class="form-group-select">
                                                                <select class="form-control" name="accessProviderInfo">
                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.accessProviderInfo')}}</option>
                                                                    <option value="2">{{__('adminPanel.read/write/delete')}}</option>
                                                                    <option value="1">{{__('adminPanel.read/write')}}</option>
                                                                    <option value="0">{{__('adminPanel.read')}}</option>
                                                                    <option value="3">{{__('adminPanel.hide')}}</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="control-label">{{__('adminPanel.accessNotification')}} <span class="text-danger">*</span></label>
                                                        <div class="form-group">
                                                            <div class="form-group-select">
                                                                <select class="form-control" name="accessNotification">
                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.accessNotification')}}</option>
                                                                    <option value="2">{{__('adminPanel.read/write/delete')}}</option>
                                                                    <option value="1">{{__('adminPanel.read/write')}}</option>
                                                                    <option value="0">{{__('adminPanel.read')}}</option>
                                                                    <option value="3">{{__('adminPanel.hide')}}</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="control-label">{{__('adminPanel.accessHolidayList')}} <span class="text-danger">*</span></label>
                                                        <div class="form-group">
                                                            <div class="form-group-select">
                                                                <select class="form-control" name="accessHolidayList">
                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.accessHolidayList')}}</option>
                                                                    <option value="2">{{__('adminPanel.read/write/delete')}}</option>
                                                                    <option value="1">{{__('adminPanel.read/write')}}</option>
                                                                    <option value="0">{{__('adminPanel.read')}}</option>
                                                                    <option value="3">{{__('adminPanel.hide')}}</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="control-label">{{__('adminPanel.accessMonetaryLog')}} <span class="text-danger">*</span></label>
                                                        <div class="form-group">
                                                            <div class="form-group-select">
                                                                <select class="form-control" name="accessMonetaryLog">
                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.accessMonetaryLog')}}</option>
                                                                    <option value="2">{{__('adminPanel.read/write/delete')}}</option>
                                                                    <option value="1">{{__('adminPanel.read/write')}}</option>
                                                                    <option value="0">{{__('adminPanel.read')}}</option>
                                                                    <option value="3">{{__('adminPanel.hide')}}</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="control-label">{{__('adminPanel.accessActivityLog')}} <span class="text-danger">*</span></label>
                                                        <div class="form-group">
                                                            <div class="form-group-select">
                                                                <select class="form-control" name="accessActivityLog">
                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.accessActivityLog')}}</option>
                                                                    <option value="2">{{__('adminPanel.read/write/delete')}}</option>
                                                                    <option value="1">{{__('adminPanel.read/write')}}</option>
                                                                    <option value="0">{{__('adminPanel.read')}}</option>
                                                                    <option value="3">{{__('adminPanel.hide')}}</option>
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
                                                                    <option value="active">Active</option>
                                                                    <option value="inactive">InActive</option>
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
                                    <th>{{__('adminPanel.isAllowAll')}}</th>
                                    <th>{{__('adminPanel.portalProviderID')}}</th>
                                    <th>{{__('adminPanel.isActive')}}</th>
                                    <th>{{__('adminPanel.accessStatus')}}</th>
                                    <th>{{__('adminPanel.createdAt')}}</th>
                                    <th>{{__('adminPanel.deletedAt')}}</th>
                                    <th>{{ __('adminPanel.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @isset($accessPolicyData) @foreach ($accessPolicyData as $key => $accessPolicy) 
                                <tr @php if ($accessPolicy->deletedAt != Null) { echo "style=background-color:#f37575";} @endphp>
                                    <td>{{$accessPolicy->name}}</td>
                                    <td>
                                        @if($accessPolicy->isAllowAll == 'true')
                                            <span class="badge badge-success" style=" padding: 4px; font-size: 13px;">{{__('adminPanel.allow')}}</span>
                                        @else
                                            <span class="badge badge-danger" style=" padding: 4px; font-size: 13px;">{{__('adminPanel.disallow')}}</span>
                                        @endif 
                                    </td>
                                    <td>
                                        @if($accessPolicy->portalProviderIDs == '')
                                        {{__('adminPanel.null')}}
                                        @else
                                            {{$accessPolicy->portalProviderIDs}}</td>
                                        @endif 
                                    <td>
                                        @if($accessPolicy->isActive == 'active')
                                        <span class="badge badge-success" style=" padding: 4px; font-size: 13px;">{{__('adminPanel.active')}}</span>
                                        @else
                                            <span class="badge badge-danger" style=" padding: 4px; font-size: 13px;">{{__('adminPanel.inactive')}}</span>
                                        @endif 
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-primary btn-sm ml-1" data-toggle="modal" data-target="#viewAccessPolicyModal{{$accessPolicy->PID}}">{{__('adminPanel.clickView')}}</i></button>
                                    
                                        <!-- Modal -->
                                        <div class="modal fade bd-12-modal-lg" id="viewAccessPolicyModal{{$accessPolicy->PID}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                            <div class="modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="exampleModalLongTitle">{{__('adminPanel.viewAccessStatus')}}</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="modal-body">
                                                                <div class="col-lg-12">
                                                                    <table class="table table-hover table-bordered" id="sampleTable">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>{{__('adminPanel.namePage')}}</th>
                                                                                <th>{{__('adminPanel.Status')}}</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <tr><td>{{__('adminPanel.accessAdminPolicy')}} </td><td> {{__("adminPanel.".getAccessStatus($accessPolicy->accessAdminPolicy))}}</td></tr>
                                                                            <tr><td>{{__('adminPanel.accessAccessPolicy')}} </td><td> {{__("adminPanel.".getAccessStatus($accessPolicy->accessAccessPolicy))}}</td></tr>
                                                                            <tr><td>{{__('adminPanel.accessAdminInformation')}} </td><td> {{__("adminPanel.".getAccessStatus($accessPolicy->accessAdminInformation))}}</td></tr>
                                                                            <tr><td>{{__('adminPanel.accessProviderList')}} </td><td> {{__("adminPanel.".getAccessStatus($accessPolicy->accessProviderList))}}</td></tr>
                                                                            <tr><td>{{__('adminPanel.accessProviderConfig')}} </td><td> {{__("adminPanel.".getAccessStatus($accessPolicy->accessProviderConfig))}}</td></tr>
                                                                            <tr><td>{{__('adminPanel.accessCurrency')}} </td><td> {{__("adminPanel.".getAccessStatus($accessPolicy->accessCurrency))}}</td></tr>
                                                                            <tr><td>{{__('adminPanel.accessBetRule')}} </td><td> {{__("adminPanel.".getAccessStatus($accessPolicy->accessBetRule))}}</td></tr>
                                                                            <tr><td>{{__('adminPanel.accessBetSetup')}} </td><td> {{__("adminPanel.".getAccessStatus($accessPolicy->accessBetSetup))}}</td></tr>
                                                                            <tr><td>{{__('adminPanel.accessInvitationSetup')}} </td><td> {{__("adminPanel.".getAccessStatus($accessPolicy->accessInvitationSetup))}}</td></tr>                                                                     </tr>
                                                                            <tr><td>{{__('adminPanel.accessProviderGameSetup')}} </td><td> {{__("adminPanel.".getAccessStatus($accessPolicy->accessProviderGameSetup))}}</td></tr>                                                                     </tr>
                                                                            <tr><td>{{__('adminPanel.accessProviderRequestList')}} </td><td> {{__("adminPanel.".getAccessStatus($accessPolicy->accessProviderRequestList))}}</td></tr>                                                                     </tr>
                                                                            <tr><td>{{__('adminPanel.accessProviderRequestBalance')}} </td><td> {{__("adminPanel.".getAccessStatus($accessPolicy->accessProviderRequestBalance))}}</td></tr>                                                                     </tr>
                                                                            <tr><td>{{__('adminPanel.accessProviderInfo')}} </td><td> {{__("adminPanel.".getAccessStatus($accessPolicy->accessProviderInfo))}}</td></tr>                                                                     </tr>
                                                                            <tr><td>{{__('adminPanel.accessNotification')}} </td><td> {{__("adminPanel.".getAccessStatus($accessPolicy->accessNotification))}}</td></tr>                                                                     </tr>
                                                                            <tr><td>{{__('adminPanel.accessHolidayList')}} </td><td> {{__("adminPanel.".getAccessStatus($accessPolicy->accessHolidayList))}}</td></tr>                                                                     </tr>
                                                                            <tr><td>{{__('adminPanel.accessMonetaryLog')}} </td><td> {{__("adminPanel.".getAccessStatus($accessPolicy->accessMonetaryLog))}}</td></tr>                                                                     </tr>
                                                                            <tr><td>{{__('adminPanel.accessActivityLog')}} </td><td> {{__("adminPanel.".getAccessStatus($accessPolicy->accessActivityLog))}}</td></tr>                                                                     </tr>
                                                                        </tbody>
                                                                    </table>    
                                                            </div>
                                                            <div class="col-lg-12">
                                                                <button class="btn btn-danger" type="button" data-dismiss="modal" aria-label="Close">{{__('adminPanel.cancel')}}</button>
                                                            </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    
                                    </td>
                                    <td>{{$accessPolicy->createdAt}}</td>
                                    <td>
                                        @if($accessPolicy->deletedAt != Null)
                                            {{$accessPolicy->deletedAt}}
                                        @else
                                        {{__('adminPanel.null')}}
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex">
                                            @if($accessPolicy->deletedAt == Null)
                                                @if($isAllowAll == 'true' || (($isAllowAll == 'false') && ($accessibility == 2)))
                                                    <form action="{{ route('vDeleteAccessPolicy') }}" method="post">
                                                        {{csrf_field()}}
                                                        <input hidden name="accessPolicyPID" value="{{$accessPolicy->PID}}">
                                                        <button class="btn btn-danger btn-sm" onclick="return confirm('@lang('adminPanel.msgDelete')')" type="submit"> <i class="fa fa-trash"></i></button>
                                                    </form>
                                                @endif
                                                @if($isAllowAll == 'true' || (($isAllowAll == 'false') && (($accessibility == 1) || ($accessibility == 2))))
                                                    <button type="button" class="btn btn-primary btn-sm ml-1" data-toggle="modal" data-target="#addAccessPolicyModal{{$accessPolicy->PID}}"><i class="fa fa-edit"></i></button>
                                                @endif
                                            @else
                                                <form action="{{ route('vRestoreAccessPolicy') }}" method="post">
                                                    {{csrf_field()}}
                                                    <input hidden name="accessPolicyID" value="{{$accessPolicy->PID}}">
                                                    <button class="btn btn-success btn-sm" onclick="return confirm('Are you sure to restore?')" type="submit"> <i class="fa fa-refresh">{{__('adminPanel.restore')}}</i></button>
                                                </form>
                                            @endif
                                        </div>

                                        <!-- Modal -->
                                        <div class="modal fade bd-12-modal-lg" id="addAccessPolicyModal{{$accessPolicy->PID}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                            <div class="modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="exampleModalLongTitle">{{__('adminPanel.editAccessPolicy')}}</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <span class="text-danger">* {{__('adminPanel.requiredIndication')}}</span><br><br>
                                                        <form action="{{route('vUpdateAccessPolicy')}}" class="UpdateAccessPolicy{{$accessPolicy->PID}}" method="post">
                                                        {{csrf_field()}}
                                                            <input hidden name="accessPolicyPID" value="{{$accessPolicy->PID}}">
                                                            <div class="row">
                                                                <div class="col-lg-12">
                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.name')}} <span class="text-danger">*</span></label>
                                                                        <div class="form-group">
                                                                            <input class="form-control" name="name" type="text" placeholder="{{__('adminPanel.name')}}" value="{{$accessPolicy->name}}">
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.isAllowAll')}} <span class="text-danger">*</span></label>
                                                                        <div class="form-group">
                                                                            <div class="form-group-select">
                                                                                <select class="form-control" name="isAllowAll">
                                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.isAllowAll')}} </option>
                                                                                    <option value="true" {{$accessPolicy->isAllowAll == 'true' ? 'selected':''}}>{{__('adminPanel.allow')}}</option>
                                                                                    <option value="false" {{$accessPolicy->isAllowAll == 'false' ? 'selected':''}}>{{__('adminPanel.disallow')}}</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.selectPortalProvider')}}</label>
                                                                        <div class="form-group">
                                                                            <div class="form-group-select">
                                                                                <select class="form-control" id="demoSelectTwo{{$accessPolicy->PID}}" multiple="" name="portalProviderID[]">
                                                                                    <optgroup label="{{__('adminPanel.selectPortalProvider')}}">
                                                                                        @foreach ($portalProviderData as $key => $portalProvider)
                                                                                            @if($portalProvider->PID !=1)
                                                                                                @php
                                                                                                    $accessProviders = explode(',', $accessPolicy->portalProviderIDs);
                                                                                                @endphp
                                                                
                                                                                                <option 
                                                                                                    value="{{$portalProvider->PID}}" 
                                                                                                        <?php 
                                                                                                            foreach($accessProviders as $eachProvider) 
                                                                                                            { 
                                                                                                                if ($eachProvider == $portalProvider->PID) {
                                                                                                                    echo "Selected";
                                                                                                                }
                                                                                                            }
                                                                                                        ?>
                                                                                                    id="accessProviderIds{{$portalProvider->PID}}">{{$portalProvider->name}}
                                                                                                </option>

                                                                                            @endif
                                                                                        @endforeach
                                                                                    </optgroup>
                                                                                </select>
                                                                                <script>
                                                                                    $(document).ready(function(){
                                                                                        var PID = {{$accessPolicy->PID}};
                                                                                        $('#demoSelectTwo'+PID).select2();
                                                                                    });
                                                                                </script>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                   

                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.accessAdminPolicy')}} <span class="text-danger">*</span></label>
                                                                        <div class="form-group">
                                                                            <div class="form-group-select">
                                                                                <select class="form-control" name="accessAdminPolicy">
                                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.accessAdminPolicy')}}</option>
                                                                                    <option value="2" {{$accessPolicy->accessAdminPolicy == '2' ? 'selected':''}}>{{__('adminPanel.read/write/delete')}}</option>
                                                                                    <option value="1" {{$accessPolicy->accessAdminPolicy == '1' ? 'selected':''}}>{{__('adminPanel.read/write')}}</option>
                                                                                    <option value="0" {{$accessPolicy->accessAdminPolicy == '0' ? 'selected':''}}>{{__('adminPanel.read')}}</option>
                                                                                    <option value="3" {{$accessPolicy->accessAdminPolicy == '3' ? 'selected':''}}>{{__('adminPanel.hide')}}</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.accessAccessPolicy')}} <span class="text-danger">*</span></label>
                                                                        <div class="form-group">
                                                                            <div class="form-group-select">
                                                                                <select class="form-control" name="accessAccessPolicy">
                                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.accessAccessPolicy')}}</option>
                                                                                    <option value="2" {{$accessPolicy->accessAccessPolicy == '2' ? 'selected':''}}>{{__('adminPanel.read/write/delete')}}</option>
                                                                                    <option value="1" {{$accessPolicy->accessAccessPolicy == '1' ? 'selected':''}}>{{__('adminPanel.read/write')}}</option>
                                                                                    <option value="0" {{$accessPolicy->accessAccessPolicy == '0' ? 'selected':''}}>{{__('adminPanel.read')}}</option>
                                                                                    <option value="3" {{$accessPolicy->accessAccessPolicy == '3' ? 'selected':''}}>{{__('adminPanel.hide')}}</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.accessAdminInformation')}} <span class="text-danger">*</span></label>
                                                                        <div class="form-group">
                                                                            <div class="form-group-select">
                                                                                <select class="form-control" name="accessAdminInformation">
                                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.accessAdminInformation')}}</option>
                                                                                    <option value="2" {{$accessPolicy->accessAdminInformation == '2' ? 'selected':''}}>{{__('adminPanel.read/write/delete')}}</option>
                                                                                    <option value="1" {{$accessPolicy->accessAdminInformation == '1' ? 'selected':''}}>{{__('adminPanel.read/write')}}</option>
                                                                                    <option value="0" {{$accessPolicy->accessAdminInformation == '0' ? 'selected':''}}>{{__('adminPanel.read')}}</option>
                                                                                    <option value="3" {{$accessPolicy->accessAdminInformation == '3' ? 'selected':''}}>{{__('adminPanel.hide')}}</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.accessProviderList')}} <span class="text-danger">*</span></label>
                                                                        <div class="form-group">
                                                                            <div class="form-group-select">
                                                                                <select class="form-control" name="accessProviderList">
                                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.accessProviderList')}}</option>
                                                                                    <option value="2" {{$accessPolicy->accessProviderList == '2' ? 'selected':''}}>{{__('adminPanel.read/write/delete')}}</option>
                                                                                    <option value="1" {{$accessPolicy->accessProviderList == '1' ? 'selected':''}}>{{__('adminPanel.read/write')}}</option>
                                                                                    <option value="0" {{$accessPolicy->accessProviderList == '0' ? 'selected':''}}>{{__('adminPanel.read')}}</option>
                                                                                    <option value="3" {{$accessPolicy->accessProviderList == '3' ? 'selected':''}}>{{__('adminPanel.hide')}}</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                               
                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.accessProviderConfig')}} <span class="text-danger">*</span></label>
                                                                        <div class="form-group">
                                                                            <div class="form-group-select">
                                                                                <select class="form-control" name="accessProviderConfig">
                                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.accessProviderConfig')}}</option>
                                                                                    <option value="2" {{$accessPolicy->accessProviderConfig == '2' ? 'selected':''}}>{{__('adminPanel.read/write/delete')}}</option>
                                                                                    <option value="1" {{$accessPolicy->accessProviderConfig == '1' ? 'selected':''}}>{{__('adminPanel.read/write')}}</option>
                                                                                    <option value="0" {{$accessPolicy->accessProviderConfig == '0' ? 'selected':''}}>{{__('adminPanel.read')}}</option>
                                                                                    <option value="3" {{$accessPolicy->accessProviderConfig == '3' ? 'selected':''}}>{{__('adminPanel.hide')}}</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.accessCurrency')}} <span class="text-danger">*</span></label>
                                                                        <div class="form-group">
                                                                            <div class="form-group-select">
                                                                                <select class="form-control" name="accessCurrency">
                                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.accessCurrency')}}</option>
                                                                                    <option value="2" {{$accessPolicy->accessCurrency == '2' ? 'selected':''}}>{{__('adminPanel.read/write/delete')}}</option>
                                                                                    <option value="1" {{$accessPolicy->accessCurrency == '1' ? 'selected':''}}>{{__('adminPanel.read/write')}}</option>
                                                                                    <option value="0" {{$accessPolicy->accessCurrency == '0' ? 'selected':''}}>{{__('adminPanel.read')}}</option>
                                                                                    <option value="3" {{$accessPolicy->accessCurrency == '3' ? 'selected':''}}>{{__('adminPanel.hide')}}</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.accessBetRule')}} <span class="text-danger">*</span></label>
                                                                        <div class="form-group">
                                                                            <div class="form-group-select">
                                                                                <select class="form-control" name="accessBetRule">
                                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.accessBetRule')}}</option>
                                                                                    <option value="2" {{$accessPolicy->accessBetRule == '2' ? 'selected':''}}>{{__('adminPanel.read/write/delete')}}</option>
                                                                                    <option value="1" {{$accessPolicy->accessBetRule == '1' ? 'selected':''}}>{{__('adminPanel.read/write')}}</option>
                                                                                    <option value="0" {{$accessPolicy->accessBetRule == '0' ? 'selected':''}}>{{__('adminPanel.read')}}</option>
                                                                                    <option value="3" {{$accessPolicy->accessBetRule == '3' ? 'selected':''}}>{{__('adminPanel.hide')}}</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.accessBetSetup')}} <span class="text-danger">*</span></label>
                                                                        <div class="form-group">
                                                                            <div class="form-group-select">
                                                                                <select class="form-control" name="accessBetSetup">
                                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.accessBetSetup')}}</option>
                                                                                    <option value="2" {{$accessPolicy->accessBetSetup == '2' ? 'selected':''}}>{{__('adminPanel.read/write/delete')}}</option>
                                                                                    <option value="1" {{$accessPolicy->accessBetSetup == '1' ? 'selected':''}}>{{__('adminPanel.read/write')}}</option>
                                                                                    <option value="0" {{$accessPolicy->accessBetSetup == '0' ? 'selected':''}}>{{__('adminPanel.read')}}</option>
                                                                                    <option value="3" {{$accessPolicy->accessBetSetup == '3' ? 'selected':''}}>{{__('adminPanel.hide')}}</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                
                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.accessInvitationSetup')}} <span class="text-danger">*</span></label>
                                                                        <div class="form-group">
                                                                            <div class="form-group-select">
                                                                                <select class="form-control" name="accessInvitationSetup">
                                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.accessInvitationSetup')}}</option>
                                                                                    <option value="2" {{$accessPolicy->accessInvitationSetup == '2' ? 'selected':''}}>{{__('adminPanel.read/write/delete')}}</option>
                                                                                    <option value="1" {{$accessPolicy->accessInvitationSetup == '1' ? 'selected':''}}>{{__('adminPanel.read/write')}}</option>
                                                                                    <option value="0" {{$accessPolicy->accessInvitationSetup == '0' ? 'selected':''}}>{{__('adminPanel.read')}}</option>
                                                                                    <option value="3" {{$accessPolicy->accessInvitationSetup == '3' ? 'selected':''}}>{{__('adminPanel.hide')}}</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.accessInvitationSetup')}} <span class="text-danger">*</span></label>
                                                                        <div class="form-group">
                                                                            <div class="form-group-select">
                                                                                <select class="form-control" name="accessInvitationSetup">
                                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.accessInvitationSetup')}}</option>
                                                                                    <option value="2" {{$accessPolicy->accessInvitationSetup == '2' ? 'selected':''}}>{{__('adminPanel.read/write/delete')}}</option>
                                                                                    <option value="1" {{$accessPolicy->accessInvitationSetup == '1' ? 'selected':''}}>{{__('adminPanel.read/write')}}</option>
                                                                                    <option value="0" {{$accessPolicy->accessInvitationSetup == '0' ? 'selected':''}}>{{__('adminPanel.read')}}</option>
                                                                                    <option value="3" {{$accessPolicy->accessInvitationSetup == '3' ? 'selected':''}}>{{__('adminPanel.hide')}}</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.accessProviderGameSetup')}} <span class="text-danger">*</span></label>
                                                                        <div class="form-group">
                                                                            <div class="form-group-select">
                                                                                <select class="form-control" name="accessProviderGameSetup">
                                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.accessProviderGameSetup')}}</option>
                                                                                    <option value="2" {{$accessPolicy->accessProviderGameSetup == '2' ? 'selected':''}}>{{__('adminPanel.read/write/delete')}}</option>
                                                                                    <option value="1" {{$accessPolicy->accessProviderGameSetup == '1' ? 'selected':''}}>{{__('adminPanel.read/write')}}</option>
                                                                                    <option value="0" {{$accessPolicy->accessProviderGameSetup == '0' ? 'selected':''}}>{{__('adminPanel.read')}}</option>
                                                                                    <option value="3" {{$accessPolicy->accessProviderGameSetup == '3' ? 'selected':''}}>{{__('adminPanel.hide')}}</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.accessProviderRequestList')}} <span class="text-danger">*</span></label>
                                                                        <div class="form-group">
                                                                            <div class="form-group-select">
                                                                                <select class="form-control" name="accessProviderRequestList">
                                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.accessProviderRequestList')}}</option>
                                                                                    <option value="2" {{$accessPolicy->accessProviderRequestList == '2' ? 'selected':''}}>{{__('adminPanel.read/write/delete')}}</option>
                                                                                    <option value="1" {{$accessPolicy->accessProviderRequestList == '1' ? 'selected':''}}>{{__('adminPanel.read/write')}}</option>
                                                                                    <option value="0" {{$accessPolicy->accessProviderRequestList == '0' ? 'selected':''}}>{{__('adminPanel.read')}}</option>
                                                                                    <option value="3" {{$accessPolicy->accessProviderRequestList == '3' ? 'selected':''}}>{{__('adminPanel.hide')}}</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.accessProviderRequestBalance')}} <span class="text-danger">*</span></label>
                                                                        <div class="form-group">
                                                                            <div class="form-group-select">
                                                                                <select class="form-control" name="accessProviderRequestBalance">
                                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.accessProviderRequestBalance')}}</option>
                                                                                    <option value="2" {{$accessPolicy->accessProviderRequestBalance == '2' ? 'selected':''}}>{{__('adminPanel.read/write/delete')}}</option>
                                                                                    <option value="1" {{$accessPolicy->accessProviderRequestBalance == '1' ? 'selected':''}}>{{__('adminPanel.read/write')}}</option>
                                                                                    <option value="0" {{$accessPolicy->accessProviderRequestBalance == '0' ? 'selected':''}}>{{__('adminPanel.read')}}</option>
                                                                                    <option value="3" {{$accessPolicy->accessProviderRequestBalance == '3' ? 'selected':''}}>{{__('adminPanel.hide')}}</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.accessProviderInfo')}} <span class="text-danger">*</span></label>
                                                                        <div class="form-group">
                                                                            <div class="form-group-select">
                                                                                <select class="form-control" name="accessProviderInfo">
                                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.accessProviderInfo')}}</option>
                                                                                    <option value="2" {{$accessPolicy->accessProviderInfo == '2' ? 'selected':''}}>{{__('adminPanel.read/write/delete')}}</option>
                                                                                    <option value="1" {{$accessPolicy->accessProviderInfo == '1' ? 'selected':''}}>{{__('adminPanel.read/write')}}</option>
                                                                                    <option value="0" {{$accessPolicy->accessProviderInfo == '0' ? 'selected':''}}>{{__('adminPanel.read')}}</option>
                                                                                    <option value="3" {{$accessPolicy->accessProviderInfo == '3' ? 'selected':''}}>{{__('adminPanel.hide')}}</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.accessNotification')}} <span class="text-danger">*</span></label>
                                                                        <div class="form-group">
                                                                            <div class="form-group-select">
                                                                                <select class="form-control" name="accessNotification">
                                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.accessNotification')}}</option>
                                                                                    <option value="2" {{$accessPolicy->accessNotification == '2' ? 'selected':''}}>{{__('adminPanel.read/write/delete')}}</option>
                                                                                    <option value="1" {{$accessPolicy->accessNotification == '1' ? 'selected':''}}>{{__('adminPanel.read/write')}}</option>
                                                                                    <option value="0" {{$accessPolicy->accessNotification == '0' ? 'selected':''}}>{{__('adminPanel.read')}}</option>
                                                                                    <option value="3" {{$accessPolicy->accessNotification == '3' ? 'selected':''}}>{{__('adminPanel.hide')}}</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.accessHolidayList')}} <span class="text-danger">*</span></label>
                                                                        <div class="form-group">
                                                                            <div class="form-group-select">
                                                                                <select class="form-control" name="accessHolidayList">
                                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.accessHolidayList')}}</option>
                                                                                    <option value="2" {{$accessPolicy->accessHolidayList == '2' ? 'selected':''}}>{{__('adminPanel.read/write/delete')}}</option>
                                                                                    <option value="1" {{$accessPolicy->accessHolidayList == '1' ? 'selected':''}}>{{__('adminPanel.read/write')}}</option>
                                                                                    <option value="0" {{$accessPolicy->accessHolidayList == '0' ? 'selected':''}}>{{__('adminPanel.read')}}</option>
                                                                                    <option value="3" {{$accessPolicy->accessHolidayList == '3' ? 'selected':''}}>{{__('adminPanel.hide')}}</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.accessMonetaryLog')}} <span class="text-danger">*</span></label>
                                                                        <div class="form-group">
                                                                            <div class="form-group-select">
                                                                                <select class="form-control" name="accessMonetaryLog">
                                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.accessMonetaryLog')}}</option>
                                                                                    <option value="2" {{$accessPolicy->accessMonetaryLog == '2' ? 'selected':''}}>{{__('adminPanel.read/write/delete')}}</option>
                                                                                    <option value="1" {{$accessPolicy->accessMonetaryLog == '1' ? 'selected':''}}>{{__('adminPanel.read/write')}}</option>
                                                                                    <option value="0" {{$accessPolicy->accessMonetaryLog == '0' ? 'selected':''}}>{{__('adminPanel.read')}}</option>
                                                                                    <option value="3" {{$accessPolicy->accessMonetaryLog == '3' ? 'selected':''}}>{{__('adminPanel.hide')}}</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="form-group">
                                                                        <label class="control-label">{{__('adminPanel.accessActivityLog')}} <span class="text-danger">*</span></label>
                                                                        <div class="form-group">
                                                                            <div class="form-group-select">
                                                                                <select class="form-control" name="accessActivityLog">
                                                                                    <option value="">{{__('adminPanel.select')}} {{__('adminPanel.accessActivityLog')}}</option>
                                                                                    <option value="2" {{$accessPolicy->accessActivityLog == '2' ? 'selected':''}}>{{__('adminPanel.read/write/delete')}}</option>
                                                                                    <option value="1" {{$accessPolicy->accessActivityLog == '1' ? 'selected':''}}>{{__('adminPanel.read/write')}}</option>
                                                                                    <option value="0" {{$accessPolicy->accessActivityLog == '0' ? 'selected':''}}>{{__('adminPanel.read')}}</option>
                                                                                    <option value="3" {{$accessPolicy->accessActivityLog == '3' ? 'selected':''}}>{{__('adminPanel.hide')}}</option>
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
                                                                                    <option value="active" {{$accessPolicy->isActive == 'active' ? 'selected':''}}>Active</option>
                                                                                    <option value="inactive" {{$accessPolicy->isActive == 'inactive' ? 'selected':''}}>InActive</option>
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
                                                var PID = {{$accessPolicy->PID}};
                                              $(".UpdateAccessPolicy"+PID).validate({
                                                rules: {
                                                    name: {
                                                        required: true
                                                    },
                                                    isAllowAll: {
                                                        required: true
                                                    },
                                                    isActive: {
                                                        required: true
                                                    },
                                                    accessAdminPolicy: {
                                                        required: true
                                                    },
                                                    accessAccessPolicy: {
                                                        required: true
                                                    },
                                                    accessAdminInformation: {
                                                        required: true
                                                    },
                                                    accessProviderList: {
                                                        required: true
                                                    },
                                                    accessProviderConfig: {
                                                        required: true
                                                    },
                                                    accessCurrency: {
                                                        required: true
                                                    },
                                                    accessBetRule: {
                                                        required: true
                                                    },
                                                    accessBetSetup: {
                                                        required: true
                                                    },
                                                    accessInvitationSetup: {
                                                        required: true
                                                    },
                                                    accessProviderGameSetup: {
                                                        required: true
                                                    },
                                                    accessProviderRequestList: {
                                                        required: true
                                                    },
                                                    accessProviderRequestBalance: {
                                                        required: true
                                                    },
                                                    accessProviderInfo: {
                                                        required: true
                                                    },
                                                    accessNotification: {
                                                        required: true
                                                    },
                                                    accessHolidayList: {
                                                        required: true
                                                    },
                                                    accessMonetaryLog: {
                                                        required: true
                                                    },
                                                    accessActivityLog: {
                                                        required: true
                                                    }
                                                },
                                                messages: {
                                                    name: {
                                                        required: "Please enter name"
                                                    },
                                                    isAllowAll: {
                                                        required: "Please Select Show Portal Providers"
                                                    },
                                                    isActive: {
                                                        required: "Please select Active Status"
                                                    },
                                                    accessAdminPolicy: {
                                                        required: "Please select Access Admin Policy"
                                                    },
                                                    accessAccessPolicy: {
                                                        required: "Please select Access Access Policy"
                                                    },
                                                    accessAdminInformation: {
                                                        required: "Please select Access Admin Information"
                                                    },
                                                    accessProviderList: {
                                                        required: "Please select Access Provider List"
                                                    },
                                                    accessProviderConfig: {
                                                        required: "Please select Access Provider Config"
                                                    },
                                                    accessCurrency: {
                                                        required: "Please select Access Currency"
                                                    },
                                                    accessBetRule: {
                                                        required: "Please select Access Bet Rule"
                                                    },
                                                    accessBetSetup: {
                                                        required: "Please select Access Bet Setup"
                                                    },
                                                    accessInvitationSetup: {
                                                        required: "Please select Access Invitation Setup"
                                                    },
                                                    accessProviderGameSetup: {
                                                        required: "Please select Access Provider Game Setup"
                                                    },
                                                    accessProviderRequestList: {
                                                        required: "Please select Access Provider Request List"
                                                    },
                                                    accessProviderRequestBalance: {
                                                        required: "Please select Access Provider Request Balance"
                                                    },
                                                    accessProviderInfo: {
                                                        required: "Please select Access Provider Info"
                                                    },
                                                    accessNotification: {
                                                        required: "Please select Access Notification"
                                                    },
                                                    accessHolidayList: {
                                                        required: "Please select Access Holiday List"
                                                    },
                                                    accessMonetaryLog: {
                                                        required: "Please select Access Monetary Log"
                                                    },
                                                    accessActivityLog: {
                                                        required: "Please select Access Activity Log"
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
<script type="text/javascript" src="{{ asset('adminPanel/js/plugins/select2.min.js')}}"></script>
<script type="text/javascript">
    $(document).ready(function(){       
        $('#demoSelect').select2();
   	    var table = $('#sampleTable').DataTable({
            responsive: true,
            dom: "<'row'<'col-sm-5'l><'col-sm-7'f>>"+"<'row'<'col-sm-12'tr>>"+"<'row'<'col-sm-5'i><'col-sm-7'p>>",
            language:{
                search: "_INPUT_",
                searchPlaceholder: "{{__('adminPanel.search')}}"
            }
        });
        $('.dataTables_filter').prepend('<label>').children().first().append('@lang('adminPanel.searchByFieldName'):<select class="form-control form-control-sm selectable"><option>@lang('adminPanel.all')</option><option value="1">@lang('adminPanel.isAllowAll')</option><option value="2">@lang('adminPanel.portalProviderID')</option><option value="3">@lang('adminPanel.isActive')</option></select>').dataTableFilter(table);
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
    .select2-search__field {
      width: 53em !important;
    }
</style>
<!-- Bet History content end -->
@endsection