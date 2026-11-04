window.AppDataTable = {

    /* 🔢 GLOBAL LENGTH SETTINGS */
    lengthMenu: [
        [50, 100, 150, 200, 500, -1],
        [50, 100, 150, 200, 500, "All"]
    ],

    pageLength: 50,

    dom:
        "<'row mb-2'<'col-md-4'l><'col-md-4 text-center'B><'col-md-4'f>>" +
        "<'row'<'col-md-12'tr>>" +
        "<'row mt-2'<'col-md-5'i><'col-md-7'p>>",

    buttons: [
        {
            extend: 'copy',
            text: '<i data-feather="copy"></i>',
            className: 'btn btn-sm btn-secondary',
            exportOptions: { columns: ':not(#no-export)' }
        },
        {
            extend: 'excel',
            text: '<i data-feather="file-text"></i>',
            className: 'btn btn-sm btn-success',
            exportOptions: { columns: ':not(#no-export)' }
        },
        {
            extend: 'pdf',
            text: '<i data-feather="file"></i>',
            className: 'btn btn-sm btn-danger',
            exportOptions: { columns: ':not(#no-export)' }
        },
        {
            extend: 'print',
            text: '<i data-feather="printer"></i>',
            className: 'btn btn-sm btn-dark',
            exportOptions: { columns: ':not(#no-export)' }
        }
    ],

    /* 🔧 FORCE ICON SIZE = 14px */
    drawCallback: function () {
        if (window.feather) {
            feather.replace({
                width: 14,
                height: 14
            });
        }
    }
};
