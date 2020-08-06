@extends('adminPanel.layout.app')
@include('adminPanel.jsIntoPhp.fullcalendarMin') 
@include('adminPanel.jsIntoPhp.momentMin') 
@include('adminPanel.jsIntoPhp.bootstrapDatepickerMin') 


@section('content')

<!-- GLOBAL MAINLY STYLES-->
<link href="{{ asset('adminPanel/calendar/vendors/bootstrap/dist/css/bootstrap.min.css')}}" rel="stylesheet" />
<link href="{{ asset('adminPanel/calendar/vendors/font-awesome/css/font-awesome.min.css')}}" rel="stylesheet" />
<link href="{{ asset('adminPanel/calendar/vendors/line-awesome/css/line-awesome.min.css')}}" rel="stylesheet" />
<link href="{{ asset('adminPanel/calendar/vendors/themify-icons/css/themify-icons.css')}}" rel="stylesheet" />
<link href="{{ asset('adminPanel/calendar/vendors/animate.css/animate.min.css')}}" rel="stylesheet" />
<link href="{{ asset('adminPanel/calendar/vendors/toastr/toastr.min.css')}}" rel="stylesheet" />
<!-- PLUGINS STYLES-->
<link href="{{ asset('adminPanel/calendar/vendors/fullcalendar/dist/fullcalendar.min.css')}}" rel="stylesheet" />
<link href="{{ asset('adminPanel/calendar/vendors/fullcalendar/dist/fullcalendar.print.min.css')}}" rel="stylesheet" media="print" />
<!-- THEME STYLES-->
<link href="{{ asset('adminPanel/calendar/css/main.min.css')}}" rel="stylesheet" />


<main class="app-content">
    <div class="app-title">
        <div>
            <h1><i class="app-menu__icon fa fa-calendar"></i> {{__('adminPanel.holidayList')}}</h1>
            <p>{{__('adminPanel.dashboardDesc')}}</p>
        </div>
        <ul class="app-breadcrumb breadcrumb side">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item active"><a href="{!!route('vHolidayList')!!}">{{__('adminPanel.holidayList')}}</a></li>
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

                 
                    <!-- START PAGE CONTENT-->
                        <div class="page-content fade-in-up">

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="ibox">
                                        <div class="ibox-head">
                                            <div class="ibox-title">{{__('adminPanel.stock')}}</div>
                                        </div>
                                        <div class="ibox-body p-3">
                                            <div id="external-events">
                                                
                                                @isset($stockData) @foreach ($stockData as $key => $stock)
                                                <div class="ex-event" data-class="{{$stock->eventColor}}"><i class="badge-point badge-{{substr($stock->eventColor,9,30)}} mr-3"></i>{{$stock->category}} - {{$stock->name}}</div>
                                                @endforeach @endisset

                                                <p class="ml-2 mt-4">
                                                    <label class="checkbox checkbox-primary">
                                                        <input id="drop-remove" type="checkbox">
                                                        <span class="input-span"></span>{{__('adminPanel.removeAfterDrop')}}</label>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <div class="ibox">
                                        <div class="ibox-head">
                                            {{-- <div class="ibox-title">CALENDAR</div> --}}
                                            <button class="btn btn-primary btn-rounded btn-air my-3" data-toggle="modal" data-target="#new-event-modal">
                                                <span class="btn-icon"><i class="la la-plus"></i>{{__('adminPanel.newEvent')}}</span>
                                            </button>
                                        </div>
                                        <div class="ibox-body">
                                            <div id="calendar"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- New Event Dialog-->
                            <div class="modal fade" id="new-event-modal" tabindex="-1" role="dialog">
                                <div class="modal-dialog" role="document">
                                    <form class="modal-content form-horizontal" id="newEventForm" action="javascript:;">
                                        <div class="modal-header p-4">
                                            <h5 class="modal-title">{{__('adminPanel.newEvent')}}</h5>
                                            <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">×</span>
                                            </button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <div class="form-group mb-4">
                                                <label class="text-muted mb-3">{{__('adminPanel.stock')}}</label>
                                                <div>
                                                    @isset($stockData) @foreach ($stockData as $key => $stock)
                                                    <label class="radio radio-outline-{{substr($stock->eventColor,9,30)}} radio-inline check-single" data-toggle="tooltip" data-original-title="{{$stock->category}} - {{$stock->name}}">
                                                    <input type="radio" name="category" data-stockID="{{$stock->PID}}" value="{{$stock->eventColor}}">
                                                        <span class="input-span"></span>
                                                    </label>
                                                    @endforeach
                                                    @endisset
                                                </div>
                                            </div>

                                            <div class="form-group mb-4">
                                                <input class="form-control form-control-line" id="new-event-title" type="text" name="title" placeholder="{{__('adminPanel.title')}}">
                                            </div>
                                            <div class="row">
                                                <div class="col-6 form-group mb-4">
                                                    <label class="col-form-label text-muted">{{__('adminPanel.start')}}:</label>
                                                    <div class="input-group-icon input-group-icon-right">
                                                        <span class="input-icon input-icon-right"><i class="fa fa-calendar-check-o"></i></span>
                                                        <input class="form-control form-control-line demoDate date" id="new-event-start" readonly type="text" name="start" value="">
                                                    </div>
                                                </div>
                                                <div class="col-6 form-group mb-4">
                                                    <label class="col-form-label text-muted">{{__('adminPanel.end')}}:</label>
                                                    <div class="input-group-icon input-group-icon-right">
                                                        <span class="input-icon input-icon-right"><i class="fa fa-calendar-check-o"></i></span>
                                                        <input class="form-control form-control-line demoDate date" id="new-event-end" readonly type="text" name="end" value="">
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- <div class="form-group mb-4 pt-3">
                                                <label class="ui-switch switch-icon mr-3 mb-0">
                                                    <input id="new-event-allDay" type="checkbox" checked>
                                                    <span></span>
                                                </label>All Day</div> --}}
                                        </div>
                                        <div class="modal-footer justify-content-start bg-primary-50">
                                            <button class="btn btn-primary btn-rounded" id="addEventButton" type="submit">{{__('adminPanel.addEvent')}}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <!-- End New Event Dialog-->
                            <!-- Event Detail Dialog-->
                            <div class="modal fade" id="event-modal" tabindex="-1" role="dialog">
                                <div class="modal-dialog" role="document">
                                    <form class="modal-content form-horizontal" id="eventForm" action="javascript:;">
                                        <div class="modal-header p-4">
                                            <h5 class="modal-title">{{__('adminPanel.editEvent')}}</h5>
                                            <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">×</span>
                                            </button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <div class="form-group mb-4">
                                                <label class="text-muted mb-3">{{__('adminPanel.stock')}}</label>
                                                <div>
                                                    @isset($stockData) @foreach ($stockData as $key => $stock)
                                                    <label class="radio radio-outline-{{substr($stock->eventColor,9,30)}} radio-inline check-single" data-toggle="tooltip" data-original-title="{{$stock->category}} - {{$stock->name}}">
                                                    <input type="radio" name="category" data-stockID="{{$stock->PID}}" value="{{$stock->eventColor}}">
                                                        <span class="input-span"></span>
                                                    </label>
                                                    @endforeach
                                                    @endisset
                                                </div>
                                            </div>
                                            <div class="form-group mb-4">
                                                <input class="form-control form-control-line" id="event-title" type="text" name="title" placeholder="Title">
                                            </div>
                                            <div class="row">
                                                <div class="col-6 form-group mb-4">
                                                    <label class="col-form-label text-muted">{{__('adminPanel.start')}}:</label>
                                                    <div class="input-group-icon input-group-icon-right">
                                                        <span class="input-icon input-icon-right"><i class="fa fa-calendar-check-o"></i></span>
                                                        <input class="form-control form-control-line demoDate date" id="event-start" readonly type="text" name="start" value="">
                                                    </div>
                                                </div>
                                                <div class="col-6 form-group mb-4">
                                                    <label class="col-form-label text-muted">{{__('adminPanel.end')}}:</label>
                                                    <div class="input-group-icon input-group-icon-right">
                                                        <span class="input-icon input-icon-right"><i class="fa fa-calendar-check-o"></i></span>
                                                        <input class="form-control form-control-line demoDate date" id="event-end" readonly type="text" name="end" value="">
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- <div class="form-group mb-4 pt-3">
                                                <label class="ui-switch switch-icon mr-3 mb-0">
                                                    <input id="event-allDay" type="checkbox">
                                                    <span></span>
                                                </label>All Day</div> --}}
                                        </div>
                                        <div class="modal-footer justify-content-between bg-primary-50">
                                            <button class="btn btn-primary btn-rounded" id="editEventButton" type="submit">{{__('adminPanel.saveChange')}}</button>
                                            <a class="text-danger" id="deleteEventButton" data-dismiss="modal"><i class="la la-trash font-20"></i></a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <!-- End Event Detail Dialog-->
                        </div>
                    <!-- END PAGE CONTENT-->
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

  <!-- CORE PLUGINS-->
  <script src="{{ asset('adminPanel/calendar/vendors/popper.js/dist/umd/popper.min.js')}}"></script>
  <script src="{{ asset('adminPanel/calendar/vendors/bootstrap/dist/js/bootstrap.min.js')}}"></script>
  <script src="{{ asset('adminPanel/calendar/vendors/metisMenu/dist/metisMenu.min.js')}}"></script>
  <script src="{{ asset('adminPanel/calendar/vendors/jquery-slimscroll/jquery.slimscroll.min.js')}}"></script>
  <script src="{{ asset('adminPanel/calendar/vendors/jquery-idletimer/dist/idle-timer.min.js')}}"></script>
  <script src="{{ asset('adminPanel/calendar/vendors/toastr/toastr.min.js')}}"></script>
  <script src="{{ asset('adminPanel/calendar/vendors/jquery-validation/dist/jquery.validate.min.js')}}"></script>
  <!-- PAGE LEVEL PLUGINS-->
  {{-- <script src="{{ asset('adminPanel/calendar/vendors/moment/min/moment.min.js')}}"></script> --}}
  {{-- <script src="{{ asset('adminPanel/calendar/vendors/fullcalendar/dist/fullcalendar.min.js')}}"></script> --}}
  @yield('momentMin')
  @yield('fullcalendarMin')
  <script src="{{ asset('adminPanel/calendar/vendors/jquery-ui/jquery-ui.min.js')}}"></script>
  <!-- CORE SCRIPTS-->
  <script src="{{ asset('adminPanel/calendar/js/app.min.js')}}"></script>
  <!-- PAGE LEVEL SCRIPTS-->
  <script src="{{ asset('adminPanel/calendar/js/scripts/calendar.js')}}"></script>

  {{-- <script type="text/javascript" src="{{ asset('adminPanel/js/plugins/bootstrap-datepicker.min.js')}}"></script> --}}
  @yield('bootstrapDatepickerMin')
  <script type="text/javascript">
  $('.demoDate').datepicker({
      format: "yyyy-mm-dd",
      autoclose: true,
      todayHighlight: true
  });
</script>

<style>
    .page-content {
    padding-top: 0px !important;
    margin-top: 0px !important;
}
</style>

@endsection