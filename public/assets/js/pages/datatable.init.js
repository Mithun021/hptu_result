"use strict";

$(document).ready(function () {

    // Basic DataTable
    $("#datatable").DataTable();

    // DataTable with length | export | search
    var tableButtons = $("#datatable-buttons").DataTable({
        lengthChange: true,
        /* ✅ REQUIRED */
        lengthMenu: [
            [50, 100, 150, 200, 500, -1],
            [50, 100, 150, 200, 500, "All"]
        ],
        pageLength: 50, // default selected
        dom: "<'row mb-2'<'col-md-4'l><'col-md-4 text-center'B><'col-md-4'f>>rtip",

        buttons: [
            {
                extend: 'copyHtml5',
                text: '<i data-feather="copy" style="width:12px;height:12px;"></i>',
                titleAttr: 'Copy',
                className: 'btn btn-sm btn-secondary me-1'
            },
            {
                extend: 'excelHtml5',
                text: '<i data-feather="file-text" style="width:12px;height:12px;"></i>',
                titleAttr: 'Excel',
                className: 'btn btn-sm btn-success me-1'
            },
            {
                extend: 'pdfHtml5',
                text: '<i data-feather="file" style="width:12px;height:12px;"></i>',
                titleAttr: 'PDF',
                className: 'btn btn-sm btn-danger me-1',
                orientation: 'landscape',
                pageSize: 'A4'
            },
            {
                extend: 'print',
                text: '<i data-feather="printer" style="width:12px;height:12px;"></i>',
                titleAttr: 'Print',
                className: 'btn btn-sm btn-dark'
            }
        ]
    });

    // Other DataTables
    $("#key-table").DataTable({ keys: true });
    $("#responsive-datatable").DataTable();
    $("#selection-datatable").DataTable({ select: { style: "multi" } });
    $("#alternative-page-datatable").DataTable({ pagingType: "full_numbers" });
    $("#scroll-vertical-datatable").DataTable({ scrollY: "350px", scrollCollapse: true, paging: false });
    $("#scroll-horizontal-datatable").DataTable({ scrollX: true });
    $("#complex-header-datatable").DataTable({ columnDefs: [{ visible: false, targets: -1 }] });
    $("#row-callback-datatable").DataTable({
        createdRow: function (row, data) {
            if (+data[5].replace(/[\$,]/g, "") > 150000) {
                $("td", row).eq(5).addClass("text-danger");
            }
        }
    });
    $("#state-saving-datatable").DataTable({ stateSave: true });
    $("#fixed-columns-datatable").DataTable({
        scrollY: 300,
        scrollX: true,
        scrollCollapse: true,
        paging: false,
        fixedColumns: true
    });
    $("#fixed-header-datatable").DataTable({ responsive: true });

    // Bootstrap styling fixes
    $(".dataTables_length select").addClass("form-select form-select-sm");
    $(".dataTables_filter input").addClass("form-control form-control-sm");
    $(".dataTables_length label").addClass("form-label");

    // Feather icons refresh
    feather.replace();
});
