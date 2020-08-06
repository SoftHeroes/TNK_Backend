@extends('adminPanel.layout.app')
@include('adminPanel.jsIntoPhp.bootstrapDatepickerMin') 
@section('content')
<!-- User Profile content start -->
<main class="app-content">
    <div class="app-title">
        <div>
            <h1><i class="app-menu__icon fa fa-th-list"></i>{{__('adminPanel.userOnlineHistory')}}</h1>
            <p>{{__('adminPanel.dashboardDesc')}}</p>
        </div>
        <ul class="app-breadcrumb breadcrumb side">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item active"><a href="{!!route('vUserOnlineHistory')!!}">{{__('adminPanel.userOnlineHistory')}}</a></li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
          <div class="tile">
            <div class="row">
                <div class="col-md-4 calender">
                   {{__('adminPanel.from')}}: <input class="form-control" id="fromDate" type="text" placeholder="{{__('adminPanel.selectDate')}}" data-date-end-date="0d" data-date-format="YYYY-mm-dd" readonly>
                </div>
                <div class="col-md-4 calender">
                    {{__('adminPanel.to')}}: <input class="form-control" id="toDate" type="text" placeholder="{{__('adminPanel.selectDate')}}" data-date-end-date="0d" data-date-format="YYYY-mm-dd" readonly>
                </div>
                <div class="col-md-2">
                    <input class="form-control calender-submit" type="button" value="{{__('adminPanel.search')}}">
                </div>
                <div class="col-md-2 date-filter">
                    <label for="date-filter">{{__('adminPanel.sortBy')}}:</label>
                    <select id="date-filter-data" class="form-control">
                        <option value="default">{{__('adminPanel.defaultWeek')}}</option>
                        <option value="day">{{__('adminPanel.day')}}</option>
                        <option value="week">{{__('adminPanel.week')}}</option>
                        <option value="month">{{__('adminPanel.month')}}</option>
                        <option value="year">{{__('adminPanel.year')}}</option>
                    </select>
                </div>
            </div>
            <div class="error user-online-date"></div>
            <div class="embed-responsive embed-responsive-16by9">
              <canvas class="embed-responsive-item" id="lineChartDemo"></canvas>
            </div>
          </div>
        </div>
    </div>
</main>
<!-- Page specific javascripts-->
<!-- Data table plugin-->
<script type="text/javascript">$('#sampleTable').DataTable();</script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script type="text/javascript" src="{{ asset('adminPanel/js/plugins/dataTables.bootstrap.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('adminPanel/js/plugins/chart.js')}}"></script>
<script type="text/javascript" src="{{ asset('adminPanel/js/popper.min.js')}}"></script>
{{-- <script type="text/javascript" src="{{ asset('adminPanel/js/plugins/bootstrap-datepicker.min.js')}}"></script> --}}
  @yield('bootstrapDatepickerMin')

<script type="text/javascript">
    $(document).ready(function() {
        var userUUID = '<?php echo $userUUID; ?>';
        var responseData = '<?php echo json_encode($activeTimeDateWise); ?>';
        var x = JSON.parse(responseData);
        var activeTime = x.activeTimeInMins;
        var date = x.Date;
        var lineChart;

        //To display the chart with the defaulf value of current week
        createChart(activeTime, date);

        //to show the calender in the input field
        $('#fromDate').datepicker({
            format: "yyyy-mm-dd",
            autoclose: true,
            todayHighlight: true
        });

        //to show the calender in the input field
        $('#toDate').datepicker({
            format: "yyyy-mm-dd",
            autoclose: true,
            todayHighlight: true
        });

        $(".calender-submit").click(function(){
            var fromDate = $('#fromDate').val();
            var toDate = $('#toDate').val();

            if (fromDate == "") {
                $('.error').text("Please enter the 'From' date");
            }

            else if (toDate == "") {
                $('.error').text("Please enter the 'To' date");
            }

            else if (toDate < fromDate) {
                $('.error').text("'To' date should be greater than 'From' date");
            } else {
                $('.error').text("");

                // Ajax request to get the response for the given date ranges
                $.ajax({
                    url: "{{ url('/admin/userOnlineHistory') }}",
                    type: 'GET',
                    data:{
                        "isUserOnlineHistoryAjax": true,
                        "userUUID" : userUUID,
                        "fromDate" : fromDate,
                        "toDate" : toDate,
                    },
                    success: function(data) {
                        var responseData = data;
                        if (responseData.activeTimeInMins == "") {
                            $('.error').text("No records to display on the Selected date range");
                        }
                        generateChartData(responseData);
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
                return responseData;
            }
        });

        $("#date-filter-data").change(function() {
            var filteredValue = $( "#date-filter-data option:selected" ).text();

            // Ajax request to get the response for the given date ranges
            $.ajax({
                    url: "{{ url('/admin/userOnlineHistory') }}",
                    type: 'GET',
                    data:{
                        "isSortByAjax": true,
                        "userUUID" : userUUID,
                        "filterBy" : filteredValue,
                    },
                    success: function(data) {
                        var responseData = data;
                        if (responseData.activeTimeInMins == "") {
                            $('.error').text("No records to display on the Selected date range");
                        }
                        generateChartData(responseData);
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
        });

        function generateChartData(responseData) {
            var activeTime2 = responseData.activeTimeInMins;
            var date2 = responseData.Date;

            createChart(activeTime2, date2);
        }

        function createChart(activeTime, date) {

            var data = {
            labels: date,
            datasets: [
                {
                    label: "My Second dataset",
                    fillColor: "rgba(151,187,205,0.2)",
                    strokeColor: "rgba(151,187,205,1)",
                    pointColor: "rgba(151,187,205,1)",
                    pointStrokeColor: "#fff",
                    pointHighlightFill: "#fff",
                    pointHighlightStroke: "rgba(151,187,205,1)",
                    data: activeTime
                }
            ]};

            if (lineChart) {
                lineChart.destroy();
            }

            var ctxl = $("#lineChartDemo").get(0).getContext("2d");
            lineChart = new Chart(ctxl).Line(data);
        }
    });
</script>
<!-- game History content end -->
@endsection
