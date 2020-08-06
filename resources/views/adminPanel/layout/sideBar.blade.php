@section('sideBar')
<!-- Sidebar Start-->
 <div class="app-sidebar__overlay" data-toggle="sidebar"></div>
    <aside class="app-sidebar"> 
        @php
            $value = session(str_replace(".","_",request()->ip()).'ECGames');
        @endphp
        <div class="app-sidebar__user">
            <div class="profileImage"></div>
            <div>
                <p class="app-sidebar__user-name firstName"></p>
                <p class="app-sidebar__user-name lastName"></p>
            </div>
        </div>

        <ul class="app-menu">
            @php
            $routeName = Route::current()->getName();
            $provider   = array('vProviderBalance','vProviderRequestBalance','vProviderGameSetup','vProviderInfo','vProviderList','vProviderConfig');
            $log   = array('vMonetaryLog','vActivityLog');
            $followUserConfig = array('vFollowBetRule','vFollowBetSetup');
            @endphp
            <li><a class="app-menu__item {{$routeName == 'vDashboard' ? 'active' : ''}}" href="{!!route('vDashboard')!!}"><i class="app-menu__icon fa fa-dashboard"></i><span class="app-menu__label">{{__('adminPanel.dashboard')}}</span></a></li>
            <li><a class="app-menu__item {{$routeName == 'vBetHistory' ? 'active' : ''}}" href="{!!route('vBetHistory')!!}"><i class="app-menu__icon fa fa-th-list"></i><span class="app-menu__label">{{__('adminPanel.betHistory')}}</span></a></li>
            <li><a class="app-menu__item {{$routeName == 'vGameHistory' ? 'active' : ''}}" href="{!!route('vGameHistory')!!}"><i class="app-menu__icon fa fa-gamepad"></i><span class="app-menu__label">{{__('adminPanel.gameHistory')}}</span></a></li>
            <li><a class="app-menu__item {{$routeName == 'vStock' ? 'active' : ''}}" href="{!!route('vStock')!!}"><i class="app-menu__icon fa fa-signal"></i><span class="app-menu__label">{{__('adminPanel.stock')}}</span></a></li>
            <li><a class="app-menu__item {{$routeName == 'vUserDetails' ? 'active' : ''}}" href="{!!route('vUserDetails')!!}"><i class="app-menu__icon fa fa-users"></i><span class="app-menu__label">{{__('adminPanel.users')}}</span></a></li>
            @if($value['isAllowAll'] == 'true' || (($value['isAllowAll'] == 'false') && ($value['accessAdminPolicy'] != 3)))
                <li><a class="app-menu__item {{$routeName == 'vAdminPolicy' ? 'active' : ''}}" href="{!!route('vAdminPolicy')!!}"><i class="app-menu__icon fa fa-user"></i><span class="app-menu__label">{{__('adminPanel.adminPolicy')}}</span></a></li>
            @endif

            @if($value['isAllowAll'] == 'true' || (($value['isAllowAll'] == 'false') && ($value['accessAccessPolicy'] != 3)))
            <li><a class="app-menu__item {{$routeName == 'vAccessPolicy' ? 'active' : ''}}" href="{!!route('vAccessPolicy')!!}"><i class="app-menu__icon fa fa-user"></i><span class="app-menu__label">{{__('adminPanel.accessPolicy')}}</span></a></li>
            @endif

            @if($value['isAllowAll'] == 'true' || (($value['isAllowAll'] == 'false') && ($value['accessAdminInformation'] != 3)))
            <li><a class="app-menu__item {{$routeName == 'vGetAdminInformation' ? 'active' : ''}}" href="{!!route('vGetAdminInformation')!!}"><i class="app-menu__icon fa fa-user-md"></i><span class="app-menu__label">{{__('adminPanel.adminInformation')}}</span></a></li>
            @endif

            <!-- new portal provider flow-->
            @if($value['isAllowAll'] == 'true' || (($value['isAllowAll'] == 'false') && (($value['accessProviderList'] != 3) || ($value['accessProviderGameSetup'] != 3) || ($value['accessProviderRequestList'] != 3) || ($value['accessProviderRequestBalance'] != 3) || ($value['accessProviderInfo'] != 3) || ($value['accessProviderConfig'] != 3))))
                <li class="treeview {{ in_array($routeName ,$provider ) ? 'is-expanded' : '' }}" ><a class="app-menu__item {{$routeName == 'vPortalProviderSelect' ? 'active' : ''}}" href="#" id="portalProviderBtn" data-toggle="treeview"><i class="app-menu__icon fa fa-users"></i><span class="app-menu__label">{{__('adminPanel.provider')}}</span><i class="treeview-indicator fa fa-angle-right"></i></a>
                    <ul class="treeview-menu">
                        @if($value['isAllowAll'] == 'true' || (($value['isAllowAll'] == 'false') && ($value['accessProviderList'] != 3)))
                            <li><a class="treeview-item {{ in_array($routeName ,['vProviderList'] ) ? 'active' : '' }}" href="{!!route('vProviderList')!!}"><i class="icon fa fa-circle-o"></i><span class="app-menu__label"> {{__('adminPanel.providerList')}}</span></a></li>
                        @endif

                        @if($value['isAllowAll'] == 'true' || (($value['isAllowAll'] == 'false') && ($value['accessProviderGameSetup'] != 3)))
                        <li><a class="treeview-item {{$routeName == 'vProviderGameSetup' ? 'active' : ''}}" href="{!!route('vProviderGameSetup')!!}"><i class="icon fa fa-circle-o"></i><span class="app-menu__label">{{__('adminPanel.providerGameSetup')}}</span></a></li>
                        @endif

                        @if($value['isAllowAll'] == 'true' || (($value['isAllowAll'] == 'false') && ($value['accessProviderRequestList'] != 3)))
                        <li><a class="treeview-item {{$routeName == 'vProviderBalance' ? 'active' : ''}}" href="{!!route('vProviderBalance')!!}"><i class="icon fa fa-circle-o"></i><span class="app-menu__label">{{__('adminPanel.providerRequestList')}}</span></a></li>
                        @endif

                        @if($value['isAllowAll'] == 'true' || (($value['isAllowAll'] == 'false') && ($value['accessProviderRequestBalance'] != 3)))
                        <li><a class="treeview-item {{$routeName == 'vProviderRequestBalance' ? 'active' : ''}}" href="{!!route('vProviderRequestBalance')!!}"><i class="icon fa fa-circle-o"></i><span class="app-menu__label"> {{__('adminPanel.providerRequestBalance')}}</span></a></li>
                        @endif

                        @if($value['isAllowAll'] == 'true' || (($value['isAllowAll'] == 'false') && ($value['accessProviderInfo'] != 3)))
                        <li><a class="treeview-item {{$routeName == 'vProviderInfo' ? 'active' : ''}}" href="{!!route('vProviderInfo')!!}"><i class="icon fa fa-circle-o"></i><span class="app-menu__label"> {{__('adminPanel.providerInfo')}}</span></a></li>
                        @endif

                        @if($value['isAllowAll'] == 'true' || (($value['isAllowAll'] == 'false') && ($value['accessProviderConfig'] != 3)))
                            <li><a class="treeview-item {{$routeName == 'vProviderConfig' ? 'active' : ''}}" href="{!!route('vProviderConfig')!!}"><i class="icon fa fa-circle-o"></i><span class="app-menu__label"> {{__('adminPanel.providerConfig')}}</span></a></li>
                        @endif
                    </ul>
                </li>
            @endif

            @if($value['isAllowAll'] == 'true' || (($value['isAllowAll'] == 'false') && ($value['accessCurrency'] != 3)))
                <li><a class="app-menu__item {{$routeName == 'vCurrency' ? 'active' : ''}}" href="{!!route('vCurrency')!!}"><i class="app-menu__icon fa fa-money"></i><span class="app-menu__label">{{__('adminPanel.currency')}}</span></a></li>
            @endif

            @if($value['isAllowAll'] == 'true' || (($value['isAllowAll'] == 'false') && ($value['accessInvitationSetup'] != 3)))
                <li><a class="app-menu__item {{$routeName == 'vInvitationSetup' ? 'active' : ''}}" href="{!!route('vInvitationSetup')!!}"><i class="app-menu__icon fa fa-cog"></i><span class="app-menu__label">{{__('adminPanel.invitationSetup')}}</span></a></li>
            @endif

            <!-- end of new portal provider flow-->
            @if($value['isAllowAll'] == 'true' || (($value['isAllowAll'] == 'false') && (($value['accessBetRule'] != 3) || ($value['accessBetSetup'] != 3))))
                <li class="treeview {{ in_array($routeName ,$followUserConfig ) ? 'is-expanded' : '' }} "><a class="app-menu__item" href="#" data-toggle="treeview"><i class="app-menu__icon fa fa-cogs"></i><span class="app-menu__label">{{__('adminPanel.followUserConfig')}}</span><i class="treeview-indicator fa fa-angle-right"></i></a>
                    <ul class="treeview-menu">
                        @if($value['isAllowAll'] == 'true' || (($value['isAllowAll'] == 'false') && ($value['accessBetRule'] != 3)))
                            <li><a class="treeview-item {{$routeName == 'vFollowBetRule' ? 'active' : ''}}" href="{!!route('vFollowBetRule')!!}"><i class="icon fa fa-circle-o"></i><span class="app-menu__label">{{__('adminPanel.followBetRule')}}</span></a></li>
                        @endif

                        @if($value['isAllowAll'] == 'true' || (($value['isAllowAll'] == 'false') && ($value['accessBetSetup'] != 3)))
                            <li><a class="treeview-item {{$routeName == 'vFollowBetSetup' ? 'active' : ''}}" href="{!!route('vFollowBetSetup')!!}"><i class="icon fa fa-circle-o"></i><span class="app-menu__label">{{__('adminPanel.followBetSetup')}}</span></a></li>
                        @endif
                    </ul>
                </li>
            @endif

            @if($value['isAllowAll'] == 'true' || (($value['isAllowAll'] == 'false') && ($value['accessNotification'] != 3)))
                <li><a class="app-menu__item {{$routeName == 'vNotification' ? 'active' : ''}}" href="{!!route('vNotification')!!}"><i class="app-menu__icon fa fa-bell"></i><span class="app-menu__label">{{__('adminPanel.notification')}}</span></a></li>
            @endif

            @if($value['isAllowAll'] == 'true' || (($value['isAllowAll'] == 'false') && ($value['accessHolidayList'] != 3)))
                <li><a class="app-menu__item {{$routeName == 'vHolidayList' ? 'active' : ''}}" href="{!!route('vHolidayList')!!}"><i class="app-menu__icon fa fa-calendar"></i><span class="app-menu__label">{{__('adminPanel.holidayList')}}</span></a></li>
            @endif

            <!-- end of new portal provider flow-->
            @if($value['isAllowAll'] == 'true' || (($value['isAllowAll'] == 'false') && (($value['accessMonetaryLog'] != 3) || ($value['accessActivityLog'] != 3))))
                <li class="treeview {{ in_array($routeName ,$log ) ? 'is-expanded' : '' }} "><a class="app-menu__item" href="#" data-toggle="treeview"><i class="app-menu__icon fa fa-edit"></i><span class="app-menu__label">{{__('adminPanel.log')}}</span><i class="treeview-indicator fa fa-angle-right"></i></a>
                    <ul class="treeview-menu">
                    @if($value['isAllowAll'] == 'true' || (($value['isAllowAll'] == 'false') && ($value['accessMonetaryLog'] != 3)))
                        <li><a class="treeview-item {{$routeName == 'vMonetaryLog' ? 'active' : ''}}" href="{!!route('vMonetaryLog')!!}"><i class="icon fa fa-circle-o"></i><span class="app-menu__label">{{__('adminPanel.monetaryLog')}}</span></a></li>
                    @endif

                    @if($value['isAllowAll'] == 'true' || (($value['isAllowAll'] == 'false') && ($value['accessActivityLog'] != 3)))
                        <li><a class="treeview-item {{$routeName == 'vActivityLog' ? 'active' : ''}}" href="{!!url('admin/activityLog?limit=300')!!}"><i class="icon fa fa-circle-o"></i><span class="app-menu__label">{{__('adminPanel.activityLog')}}</span></a></li>
                    @endif
                    </ul>
                </li>
            @endif
        </ul>
    </aside>

    {{--  get admin Info Profiles  --}}
    <script>
        $(document).ready(function(){
           $.ajaxSetup({headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")}}); 
           $.ajax({
               url: "{{ route('vInfoProfiles') }}",
               method: "post",
               data: {ajaxData:1},
               success: result => {
                  if(result.data[0].profileImage != null){
                        $('.profileImage').html("<img class='app-sidebar__user-avatar' style='background: white;' src='/"+ result.data[0].profileImage +"' alt='User Image'>");
                  } else{
                        $('.profileImage').html("<i class='fa fa-user-circle' style='font-size: 50px;margin-right: 5px;' aria-hidden='true'></i>");
                  }
                        $('.firstName').html(result.data[0].firstName);
                        $('.lastName').html(result.data[0].lastName);
               },
               error: function (error) {
                    alert('error; ' + eval(error));
               }
           });

            let portalProviderBtnRef = $("#portalProviderBtn");
            let portalProviderSelectionModalRef = $("#portalProviderSelectionModal");

            if(portalProviderBtnRef && portalProviderSelectionModalRef){
                portalProviderBtnRef.click(function(){
                    portalProviderSelectionModalRef.modal();
                })
            }

        });
   </script>
<!-- Sidebar End-->
@endsection

