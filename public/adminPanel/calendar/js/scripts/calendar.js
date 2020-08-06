! function($) {
    "use strict";

    var CalendarApp = function() {
        this.$body = $("body")
        this.$calendar = $('#calendar'),
            this.$event = ('#external-events div.ex-event'),
            this.$categoryForm = $('#add-new-event form'),
            this.$extEvents = $('#calendar-events'),
            this.$modal = $('#new-event-modal'),
            this.$eventModal = $('#event-modal'),
            this.$saveCategoryBtn = $('.save-category'),
            this.$calendarObj = null
    };

    // handler for clicking on an empty calendar field
    CalendarApp.prototype.onSelect = function(start, end, allDay) {
            var $this = this;
            $this.$modal.modal();
            //// fill in the values
            this.$modal.find('#new-event-start').val($.fullCalendar.formatDate(start, "YYYY-MM-DD"));
            this.$modal.find('#new-event-end').val($.fullCalendar.formatDate(end, "YYYY-MM-DD"));

            $this.$calendarObj.fullCalendar('unselect');
        },

        // Update event
        CalendarApp.prototype.updateEvent = function(calEvent, revertFunc) {
            // The same can be done for eventDrop and eventResize
            var $this = this;
            $this.$eventModal.modal();
            // fill in the values
            $this.$eventModal.find('#event-title').val(calEvent.title);
            $this.$eventModal.find('#event-start').val($.fullCalendar.formatDate(calEvent.start, "YYYY-MM-DD"));
            $this.$eventModal.find('#event-end').val(calEvent.end ? $.fullCalendar.formatDate(calEvent.end, "YYYY-MM-DD") : '');
            if (calEvent.className.length) $this.$eventModal.find('input[name="category"][value="' + calEvent.className + '"]').prop("checked", true);
            else $this.$eventModal.find('#event-color :first-child').prop("selected", true);
            $this.$eventModal.find('#event-allDay').prop("checked", calEvent.allDay);

            // set the handler to delete the event
            $this.$eventModal.find('#deleteEventButton').unbind('click').click(function() {

                // execute the query to remove the event from the database
                $.ajax({
                    url: "/admin/deleteHolidayList",
                    method: "post",
                    data: { id: calEvent.id },
                    headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
                    success: result => {
                        // console.log(result);
                        if (result.status == true) {
                            // Remove Event
                            $this.$calendarObj.fullCalendar('removeEvents', function(ev) {
                                return (ev._id == calEvent._id);
                            });

                            toastr.success('event successfully deleted');
                            $this.$eventModal.modal('hide');
                        }
                    },
                    error: function(er) {
                        console.log(er);
                        toastr.error('event error deleted');
                    }
                });


            });

            // set the handler to update the event
            $this.$eventModal.find('form').unbind('submit').on('submit', function() {
                if ($('#eventForm').valid()) {
                    var event = {};
                    calEvent.title = event.title = $(this).find("#event-title").val();
                    calEvent.start = event.start = $(this).find("#event-start").val();
                    var End = $(this).find("#event-end").val();
                    if (End != "" || End != undefined) {
                        calEvent.end = event.end = $(this).find("#event-end").val();
                        calEvent.stick = event.stick = false;
                    } else {
                        calEvent.end = event.end = "";
                        calEvent.stick = event.stick = true;
                    }

                    calEvent.className = event.className = $(this).find('input[name="category"]:checked').val();
                    event.stockID = CheckStockIDByColor(event.className);
                    calEvent.allDay = event.allDay = true;
                    // execute the query to update the event in the database

                    $.ajax({
                        url: "/admin/updateHolidayList",
                        method: "post",
                        headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
                        data: { event: event, id: calEvent.id },
                        success: result => {
                            // console.log(result);
                            if (result.status == true) {
                                // update event
                                $this.$calendarObj.fullCalendar('updateEvent', event);
                                toastr.success('event successfully updated');

                                $this.$eventModal.modal('hide');
                            }
                        },
                        error: function(er) {
                            console.log(er);
                            toastr.error('event error updated');
                        }
                    });

                }
            });
        }

    // Called when a valid jQuery UI draggable has been dropped onto the calendar.
    CalendarApp.prototype.onDrop = function(eventObj, date) {
            var $this = this;
            // retrieve the dropped element's stored Event Object
            var originalEventObject = eventObj.data('eventObject');
            // we need to copy it, so that multiple events don't have a reference to the same object
            var copiedEventObject = $.extend({}, originalEventObject);
            //var $categoryClass = eventObj.attr('data-class');
            // assign it the date that was reported
            copiedEventObject.start = $.fullCalendar.formatDate(date, "YYYY-MM-DD");
            // execute the query to save the event in the database and get its id

            copiedEventObject.stockID = CheckStockIDByColor(copiedEventObject.className);
            copiedEventObject.id = Math.random();
            copiedEventObject.end = copiedEventObject.start;

            $.ajax({
                url: "/admin/createHolidayList",
                method: "post",
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
                data: { event: copiedEventObject },
                success: result => {
                    // console.log(result);
                    if (result.status == true) {
                        // Create event
                        $this.$calendarObj.fullCalendar('renderEvent', copiedEventObject, true); // stick? = true
                        toastr.success('event successfully created');
                    }
                },
                error: function(er) {
                    console.log(er);
                    toastr.error('event error created');
                }
            });


            // is the "remove after drop" checkbox checked?
            if ($('#drop-remove').is(':checked')) {
                // if so, remove the element from the "Draggable Events" list
                eventObj.remove();
            }
        },

        // initialize the external events
        CalendarApp.prototype.enableDrag = function() {
            $(this.$event).each(function() {
                // store data so the calendar knows to render an event upon drop
                $(this).data('eventObject', {
                    title: $.trim($(this).text()), // use the element's text as the event title
                    stick: true, // maintain when user navigates (see docs on the renderEvent method)
                    className: $(this).attr('data-class')
                });

                // make the event draggable using jQuery UI
                $(this).draggable({
                    zIndex: 999,
                    revert: true, // will cause the event to go back to its
                    revertDuration: 0 //  original position after the drag
                });
            });
        }

    /* Initializing */
    CalendarApp.prototype.init = function() {
        this.enableDrag();
        var $this = this;
        $this.$calendarObj = $this.$calendar.fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay,listWeek'
            },
            events: "/admin/holidayLists",
            editable: true,
            droppable: true, // this allows things to be dropped onto the calendar
            navLinks: true, // can click day/week names to navigate views
            eventLimit: true, // allow "more" link when too many events
            selectable: true,
            drop: function(date) { $this.onDrop($(this), date); },
            select: function(start, end, allDay) { $this.onSelect(start, end, allDay); },
            eventClick: function(calEvent, jsEvent, view) { $this.updateEvent(calEvent); },
            // The same can be done for these events
            eventResize: function(event, delta, revertFunc) { $this.updateEvent(event, revertFunc); },
            eventDrop: function(event, delta, revertFunc) { $this.updateEvent(event, revertFunc); },
            eventRender: function(event, element, view) {
                event.allDay = event.allDay == true ? true : false;
            },
        });
    }


    // initializing CalendarApp

    $.CalendarApp = new CalendarApp;
    $.CalendarApp.init();

    // initialize datetimepicker
    // $('.datepicker').datetimepicker({
    //     format: "yyyy-mm-dd",
    //     autoclose: true,
    //     todayHighlight: true
    // });

    // Validate Forms
    $('#newEventForm').validate({
        errorClass: "help-block",
        rules: {
            title: { required: true },
            start: { required: true },
        },
        highlight: function(e) { $(e).closest(".form-group").addClass("has-error") },
        unhighlight: function(e) { $(e).closest(".form-group").removeClass("has-error") },
    });

    $('#eventForm').validate({
        errorClass: "help-block",
        rules: {
            title: { required: true },
            start: { required: true },
        },
        highlight: function(e) { $(e).closest(".form-group").addClass("has-error") },
        unhighlight: function(e) { $(e).closest(".form-group").removeClass("has-error") },
    });


    // Handler to add new event 
    $('#newEventForm').submit(function() {
        if ($(this).valid()) {
            var CalendarApp = $.CalendarApp;
            var newEvent = {
                id: Math.random(),
                stockID: CheckStockIDByColor($('input[name="category"]:checked').val()),
                title: $('#new-event-title').val(),
                start: CalendarApp.$modal.find('#new-event-start').val(),
                end: CalendarApp.$modal.find('#new-event-end').val(),
                allDay: true, // CalendarApp.$modal.find('#new-event-allDay').prop('checked'),
                className: CalendarApp.$modal.find('input[name="category"]:checked').val(),
                stick: false
            }

            // execute the query to save the event in the database and get its id

            $.ajax({
                url: "/admin/createHolidayList",
                method: "post",
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
                data: { event: newEvent },
                success: result => {
                    // console.log(result);
                    if (result.status == true) {
                        // Create Event
                        CalendarApp.$calendarObj.fullCalendar('renderEvent', newEvent, true); // stick? = true
                        toastr.success('event successfully created');
                        CalendarApp.$modal.modal('hide');
                    }
                },
                error: function(er) {
                    console.log(er);
                    toastr.error('event error created');
                }
            });
        }
    });

    function CheckStockIDByColor(color) {

        if (color == 'fc-event-primary') { return 1 } else if (color == 'fc-event-warning') { return 2 } else if (color == 'fc-event-success') { return 3 } else if (color == 'fc-event-danger') { return 4 } else if (color == 'fc-event-info') { return 5 } else if (color == 'fc-event-secondary') { return 6 } else if (color == 'fc-event-pink') { return 7 } else if (color == 'fc-event-blue') { return 8 } else if (color == 'fc-event-bright-purple') { return 9 }

    }

}(window.jQuery);