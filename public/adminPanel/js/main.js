(function () {
    "use strict";

    var treeviewMenu = $(".app-menu");

    // Toggle Sidebar
    $('[data-toggle="sidebar"]').click(function (event) {
        event.preventDefault();
        $(".app").toggleClass("sidenav-toggled");
    });

    // Activate sidebar treeview toggle
    $("[data-toggle='treeview']").click(function (event) {
        event.preventDefault();
        if (!$(this).parent().hasClass("is-expanded")) {
            treeviewMenu
                .find("[data-toggle='treeview']")
                .parent()
                .removeClass("is-expanded");
        }
        $(this).parent().toggleClass("is-expanded");
    });

    // Set initial active toggle
    $("[data-toggle='treeview.'].is-expanded")
        .parent()
        .toggleClass("is-expanded");

    //Activate bootstrip tooltips
    $("[data-toggle='tooltip']").tooltip();

    // Applying functions.
    $.fn.extend({
        dataTableFilter: function (ext) {
            var $this = this;
            $this.children(".selectable").on("change", function () {
                ext.search("").columns().search("").draw();
                $(".dataTables_filter input").val("");
            });
            $this.next().on("keyup", function (e) {
                var term = $this.children(".selectable").val();
                if ($.isNumeric(term)) {
                    ext.column(term).search(e.target.value).draw();
                }
            });
        },
    });
})();

function saveIncludeDeletedSession(saveSessionUrl, updateSessionUrl) {
    $.ajax({
        url: saveSessionUrl,
        type: "POST",
        dataType: "json",
        contentType: "application/json",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            if (response.status) {
                window.location.href = updateSessionUrl;
            } else {
                alert(response.message);
            }
        },
        error: function () {
            console.log(response);
        },
    });
}

function removeIncludeDeletedSession(removeSessionUrl, updateSessionUrl) {
    $.ajax({
        url: removeSessionUrl,
        type: "POST",
        dataType: "json",
        contentType: "application/json",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            if (response.status) {
                window.location.href = updateSessionUrl;
            } else {
                alert(response.message);
            }
        },
        error: function () {
            console.log(response);
        },
    });
}
