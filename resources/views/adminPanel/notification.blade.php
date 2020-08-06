@extends('adminPanel.layout.app') @section('content')
<!-- Bet History content start -->
<main class="app-content">
    <div class="app-title">
        <div>
            <h1><i class="app-menu__icon fa fa-th-list"></i> {{__('adminPanel.notification')}}</h1>
            <p>{{__('adminPanel.dashboardDesc')}}</p>
        </div>
        <ul class="app-breadcrumb breadcrumb side">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item active"><a href="{!!route('vNotification')!!}">{{__('adminPanel.notification')}}</a></li>
        </ul>
    </div>
    @php
    $value = session(str_replace(".","_",request()->ip()).'ECGames');
    @endphp

    <div class="row">
        <div class="col-md-12">
            <div class="tile">
                <div class="tile-body">
                    <div class="table-responsive">
                        <!-- Button trigger modal -->
                        @if($isAllowAll == 'true' || (($isAllowAll == 'false') && (($accessibility == 1) || ($accessibility == 2))))
                            <div class="form-group col-md-4">
                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addNotificationModal">
                                {{__('adminPanel.newNotification')}} <i class="fa fa-plus"></i>
                                </button>
                            </div>
                        @endif

                        <!-- Modal -->
                        <div class="modal fade bd-12-modal-lg" id="addNotificationModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLongTitle">{{__('adminPanel.newNotification')}}</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <span class="text-danger">* {{__('adminPanel.requiredIndication')}}</span><br><br>
                                        <form action="{{route('vCreateNotification')}}" class="createNotification" method="post">
                                            {{csrf_field()}}
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    @if ($checkProviderID == 0)
                                                        <input name="portalProviderID[]" value="{{$value['portalProviderID']}}" hidden>
                                                    @else
                                                        <div class="form-group">
                                                            <label class="control-label">{{__('adminPanel.selectPortalProvider')}}</label>
                                                            <div class="form-group">
                                                                <div class="form-group-select">
                                                                    <select class="form-control" id="demoSelect" multiple="" name="portalProviderID[]">
                                                                        <optgroup label="{{__('adminPanel.selectPortalProvider')}}">
                                                                            @foreach ($portalProviderData as $key => $portalProvider)
                                                                            @if($portalProvider->PID != 1)
                                                                                <option value="{{$portalProvider->PID}}">{{$portalProvider->name}}</option>
                                                                            @endif
                                                                            @endforeach
                                                                        </optgroup>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    <div class="form-group">
                                                        <label class="control-label">{{__('adminPanel.title')}} <span class="text-danger">*</span></label>
                                                        <div class="form-group">
                                                            <input class="form-control" name="title" type="text" placeholder="{{__('adminPanel.title')}}">
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="control-label">{{__('adminPanel.message')}} <span class="text-danger">*</span></label>
                                                        <div class="form-group">
                                                            <textarea class="form-control" name="message" rows="4" placeholder="{{__('adminPanel.message')}}"></textarea>
                                                        </div>
                                                    </div>


                                                </div>
                                            </div>
                                            <div class="col-lg-12">
                                                <button class="btn btn-primary" type="submit">{{__('adminPanel.sendNotification')}}</button>
                                                <button class="btn btn-danger" type="reset" data-dismiss="modal" aria-label="Close">{{__('adminPanel.cancel')}}</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <table class="table table-hover table-bordered" id="sampleTable">
                            <tbody>
                              
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th> {{__('adminPanel.notificationID')}} </th>
                                    <th> {{__('adminPanel.portalProviderName')}} </th>
                                    <th> {{__('adminPanel.portalProviderID')}} </th>
                                    <th> {{__('adminPanel.notificationFromUserID')}} </th>
                                    <th> {{__('adminPanel.notificationToUserID')}} </th>
                                    <th> {{__('adminPanel.type')}} </th>
                                    <th> {{__('adminPanel.title')}} </th>
                                    <th> {{__('adminPanel.message')}} </th>
                                    <th> {{__('adminPanel.createdAt')}} </th>
                                    <th> {{__('adminPanel.deletedAt')}} </th>
                                    <th> </th>
                                </tr>
                            </tfoot>
                        </table>

                        {{-- confirm Modal Delete --}}
                        <div class="modal fade bd-6-modal-lg" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLongTitle">{{__('adminPanel.confirmation')}}</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row"> 
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                     <h4 align="center" style="margin:0;" class="modal-message"></h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <button class="btn btn-primary" name="ok_button" id="ok_button" type="submit">{{__('adminPanel.confirm')}}</button>
                                            <button class="btn btn-danger" type="reset" data-dismiss="modal" aria-label="Close">{{__('adminPanel.cancel')}}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- modal update Notification  --}}
                        <div class="modal fade bd-12-modal-lg" id="updateNotificationModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLongTitle">{{__('adminPanel.editAdminPolicy')}}</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    </div>
                                    <div class="modal-body">
                                        @php
                                        $value = session(str_replace(".","_",request()->ip()).'ECGames');
                                        @endphp
                                        <!-- Tab panes -->
                                        <div class="tab-content">
                                            <div role="tabpanel" class="tab-pane fade in active show" id="Information">
                                                <span class="text-danger">* {{ __('adminPanel.requiredIndication') }}</span><br><br>
                                                <form action="{{route('vUpdateNotification')}}" method="POST" class="UpdateNotification" enctype="multipart/form-data">
                                                    {{csrf_field()}}
                                                    <input hidden name="notificationID" id="hidden_notificationID" value="">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <div class="form-group">
                                                                <label class="control-label">{{__('adminPanel.title')}} <span class="text-danger">*</span></label>
                                                                <div class="form-group">
                                                                    <input class="form-control" name="title" id="title" type="text" placeholder="{{__('adminPanel.title')}}" value="">
                                                                </div>
                                                            </div>

                                                            <div class="form-group">
                                                                <label class="control-label">{{__('adminPanel.message')}} <span class="text-danger">*</span></label>
                                                                <div class="form-group">
                                                                    <textarea class="form-control" name="message" id="message" rows="4" placeholder="{{__('adminPanel.message')}}"></textarea>
                                                                </div>
                                                            </div>
                                                            @if ($checkProviderID == 0)
                                                                <input name="portalProviderID" id="checkProvider" value="{{$value['portalProviderID']}}" hidden>
                                                            @else
                                                            <div class="form-group">
                                                                <label class="control-label">{{ __('adminPanel.portalProviderName') }} <span class="text-danger">*</span></label>
                                                                <div class="form-group">
                                                                    <div class="form-group-select">
                                                                        <select class="form-control" name="portalProviderID" id="select_portalProviderID">
                                                                            <option value="">{{ __('adminPanel.selectPortalProvider') }}</option>
                                                                            @foreach ($portalProviderData as $portalProvider)
                                                                                @if ($portalProvider->PID != "1") {
                                                                                <option value="{{$portalProvider->PID}}">{{$portalProvider->name}}</option>
                                                                                @endif
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @endif
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<input hidden id="checkProviderID" value="{{$checkProviderID}}">

<!-- Page specific javascripts-->
<!-- Data table plugin-->
<script type="text/javascript" src="{{ asset('adminPanel/js/plugins/dataTables.bootstrap.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('adminPanel/js/plugins/select2.min.js')}}"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $('#demoSelect').select2();
        // Filter adding.
        var checkProviderID = $('#checkProviderID').val();
        var table = $('#sampleTable').DataTable({
            responsive: true,
            dom: "<'row'<'col-sm-5'l><'col-sm-7'f>>"+"<'row'<'col-sm-12'tr>>"+"<'row'<'col-sm-5'i><'col-sm-7'p>>",
            language:{
                search: "_INPUT_",
                searchPlaceholder: "{{__('adminPanel.search')}}"
            },
            processing: true,
			serverSide: true,
            ajax: '{{route("vNotification")}}',
            columns:[
				{ title: "@lang('adminPanel.notificationID')",data: "notificationUUID",name: "notification.UUID"},
				{ title: "@lang('adminPanel.portalProviderName')", data: "portalProviderName", name: "portalProvider.name" },
				{ title: "@lang('adminPanel.portalProviderID')", data: "portalProviderUUID", name: "portalProvider.UUID" },
				{ title: "@lang('adminPanel.notificationFromUserID')", data: "notificationFromID", name:"u1.UUID"},
				{ title: "@lang('adminPanel.notificationToUserID')", data: "notificationToID", name: "u2.UUID" },
				{ title: "@lang('adminPanel.type')", data: "type", name: "notification.type" },
                { title: "@lang('adminPanel.title')", data: "title", name: "notification.title" },
                { title: "@lang('adminPanel.message')", data: "message", name: "notification.message" },
                { title: "@lang('adminPanel.createdAt')", data: "createdAt", name: "notification.createdAt" },
                { title: "@lang('adminPanel.deletedAt')", data: "deletedAt", name: "notification.deletedAt" },
				{ title: "@lang('adminPanel.action')", data: 'action', name: 'action', orderable: false, searchable: false}
            ],
            initComplete: function () {
            this.api().columns().every( function (e) {
                var column = this;
                if (e == 5) {
                    var select = $('<select><option value=""></option></select>')
                    .appendTo( $(column.footer()).empty() )
                    .on( 'change', function () {
                        var val = $.fn.dataTable.util.escapeRegex($(this).val());
                        column.search( val, true, false ).draw();
                    } );
                    
                    column.data().unique().sort().each( function () {
                    select.html( '<option value="">All</option><option value="0">admin</option><option value="1">follow</option><option value="2">unFollow</option><option value="3">balance Update</option><option value="4">welcome</option>' )
                } );
                } else if (e == 10){
                    var select = $('');
                    column.data().unique().sort().each( function ( d, j ) {
                    select.append( )
                } );
                } else {
                    var select = $('<input name="" value="">')
                    .appendTo( $(column.footer()).empty() )
                    .on( 'change', function () {
                        var val = $.fn.dataTable.util.escapeRegex( $(this).val() );
                        column.search( val, true, false ).draw();
                    } );
                    
                    column.data().unique().sort().each( function ( d, j ) {
                    select.append( '<option name="'+d+'" value="'+d+'">' )
                } );
                }
            });
        }
        });

        $('.dataTables_filter').append('<br><button class="btn" onClick="window.location.reload();"><i class="fa fa-refresh" aria-hidden="true"></i>@lang('adminPanel.refresh')</button>').dataTableFilter(table);
        $('.dataTables_filter').prepend('<input type="checkbox" id="includeDeleted" value="true"> <label>@lang('adminPanel.includeDeleted')</label>').children().first().dataTableFilter(table);
       
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


        $(document).on('click', '.edit', function(){
            var id = $(this).attr('id');
            $('#form_result').html('');
                $.ajax({
                url:"{{route('getUpdateNotification')}}",
                data:{notificationID:id},
                type:"post",
                dataType:"json",
                success:function(data)
                {
                    if(checkProviderID != 0){
                    document.getElementById("select_portalProviderID").options.selectedIndex = data[0].portalProviderID-1;
                    }
                    $('#message').val(data[0].message);
                    $('#title').val(data[0].title);
                    $('#hidden_notificationID').val(id);
                    $('#updateNotificationModal').modal('show');
                }
            })
        });

        // Delete Notification
        var notificationID;
        var action;
        var deleteConfirmationMsg = @json( __('adminPanel.msgDelete') );
        var restoreConfirmationMSg = @json( __('adminPanel.msgRestore') );

        $(document).on('click', '.delete', function(){
            notificationID = $(this).attr('id');
            action = "delete";
            $('#confirmModal').modal('show');
        });

        $('#confirmModal').on('show.bs.modal', function (event) {
            var modal = $(this)
            if (action == "delete")
                modal.find('.modal-message').html(deleteConfirmationMsg);
            else 
                modal.find('.modal-message').html(restoreConfirmationMSg);
        });

        // Restore Notification
        $(document).on('click', '.restore', function(){
            notificationID = $(this).attr('id');
            action = "restore";
            $('#confirmModal').modal('show');
        });

        $('#ok_button').click(function(){
            $.ajax({
                url: (action == "delete") ? "{{route('vDeleteNotification')}}" : "{{route('vRestoreNotification')}}",
                data:{notificationID:notificationID},
                type:"post",
                beforeSend:function(){
                    $('#ok_button').text((action == "delete") ? "@lang('adminPanel.deleting')..." : "@lang('adminPanel.restoring')...");
                },
                success:function(data){
                        setTimeout(function(){
                        $('#confirmModal').modal('hide');
                        $('#sampleTable').DataTable().ajax.reload();
                        $('#ok_button').text("@lang('adminPanel.confirm')");
                        }, 2000);
                }
            })
        });
    });
    
    $(document).ready(function(){
        $(".UpdateNotification").validate({
            rules: {
                title: {
                    required: true
                },
                message: {
                    required: true
                },
                portalProviderID: {
                    required: true
                }
            },
            messages: {
                title: {
                    required: "Please enter the title"
                },
                message: {
                    required: "Please enter the message"
                },
                portalProviderID: {
                    required: "Please select portal Providers"
                }
            },

            errorPlacement: function(error, element) {
                error.insertAfter(element.parent());
            }
        });
    });
</script>
<style>
em{
    color: red;
}
.select2-search__field {
    width: 54em !important;
}

   
    #includeDeleted{
        display: block;
        position: absolute;
        margin-top: 9px;
        right: 281px;
    }
</style>
<!-- Bet History content end -->
@endsection
