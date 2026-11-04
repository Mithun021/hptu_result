window.ResultAppDataTable = {

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

        /* EXCEL */
        {
            extend: 'excel',
            className: 'btn btn-sm btn-success',
            text: '<i data-feather="file-text"></i>',
            // exportOptions: { columns: ':visible' },
            // exportOptions: {
            //     columns: function (idx, data, node) {
            //         return $(node).attr('id') !== 'no-export';
            //     }
            // },
            exportOptions: {

                // 🔹 Column level filter (no-export in th/td)
                columns: function (idx, data, node) {

                    let $header = $(node);

                    if ($header.hasClass('no-export') || $header.attr('id') === 'no-export') {
                        return false;
                    }

                    return true;
                },

                // 🔹 Body level cleanup
                format: {
                    body: function (data, row, column, node) {

                        let $node = $(node);

                        // Agar poora td no-export hai
                        if ($node.hasClass('no-export') || $node.attr('id') === 'no-export') {
                            return '';
                        }

                        // Excel me no-print remove nahi karna (as per requirement)
                        return $node.text().trim();
                    }
                }
            },
            title: null,
            messageTop: function () {
                return getExcelHeader();
            }
        },

        /* PRINT */
        {
            extend: 'print',
            className: 'btn btn-sm btn-dark',
            text: '<i data-feather="printer"></i>',
            // exportOptions: { columns: ':visible' },
            // exportOptions: {
            //     columns: function (idx, data, node) {
            //         return $(node).attr('id') !== 'no-export';
            //     }
            // },
            exportOptions: {

                // 🔹 Column level filter (no-export hide)
                columns: function (idx, data, node) {

                    let $header = $(node);

                    if ($header.hasClass('no-export') || $header.attr('id') === 'no-export') {
                        return false;
                    }

                    return true;
                },

                // 🔹 Body level cleanup (important part)
                format: {
                    body: function (data, row, column, node) {

                        let $node = $(node).clone();

                        // 1️⃣ Agar pura td no-export hai
                        if ($node.hasClass('no-export') || $node.attr('id') === 'no-export') {
                            return '';
                        }

                        // 2️⃣ Agar cell ke andar kisi bhi tag me no-print hai → remove it
                        $node.find('.no-print, #no-print').remove();

                        return $node.text().trim();
                    }
                }
            },
            title: '',
            messageTop: function () {
                return getPrintHeader();
            },
            customize: function (win) {

                $(win.document.body).find('h1').remove();
                $(win.document.body).css('font-size', '10pt');
                $(win.document.body).find('div').first().css('text-align', 'center');
            }
        }
    ],

    drawCallback: function () {
        if (window.feather) {
            feather.replace({
                width: 14,
                height: 14
            });
        }
    }
};


/* ===============================
   PRINT HEADER (HTML ALLOWED)
=================================*/
function getPrintHeader() {

    return `
        <h5 style="margin:0;">CONTROLLER OF EXAMINATIONS</h5>
        <h5 style="margin:0;">GURU ANGAD DEV VETERINARY AND ANIMAL SCIENCE UNIVERSITY, LUDHIANA (INDIA)</h5>
        <h5>${window.instituteName}</h5>
        <h5 style="margin:10px 0 0 0;">${$('#exam_title').text()}</h5>

        <h5 style="margin:5px 0;">
            ${$('#semester_text').text()} - Session: ${$('#session_text').text()}
        </h5>

        <p style="margin:5px 0;">
            <b>Subject Name : ${$('#subject_text').text()}</b>
        </p>

        <p style="margin:5px 0 15px 0;">
            <b>Maximum Marks : ${$('#max_marks_text').text()}</b>
        </p>
    `;
}


/* ===============================
   EXCEL HEADER (PLAIN TEXT ONLY)
=================================*/
function getExcelHeader() {

    return [
        "CONTROLLER OF EXAMINATIONS",
        "GURU ANGAD DEV VETERINARY AND ANIMAL SCIENCE UNIVERSITY, LUDHIANA (INDIA)",
        window.instituteName,
        "",
        $('#exam_title').text(),
        "",
        $('#semester_text').text() + " - Session: " + $('#session_text').text(),
        "",
        "Subject Name : " + $('#subject_text').text(),
        "Maximum Marks : " + $('#max_marks_text').text()
    ].join("\n");
}