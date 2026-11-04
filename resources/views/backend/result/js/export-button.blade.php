<script>
// $("#print-btn").click(function () {

//     var content = $(".result-table").prop('outerHTML');

//     var printWindow = window.open('', '', 'width=1200,height=800');

//     printWindow.document.write(`
//         <html>
//         <head>
//             <title>Result Print</title>

//             <style>

//                 @page{
//                     size: A3 landscape;
//                     margin:10mm;
//                 }

//                 body{
//                     font-family: Arial;
//                 }

//                 table{
//                     width:100%;
//                     border-collapse: collapse;
//                 }

//                 th,td{
//                     border:1px solid #000;
//                     padding:6px;
//                     text-align:center;
//                 }

//                 h4{
//                     margin:2px;
//                 }

//                 .no-export{
//                     display:none !important;
//                 }

//             </style>

//         </head>

//         <body>

//             ${content}

//         </body>

//         </html>
//     `);

//     printWindow.document.close();
//     printWindow.focus();
//     printWindow.print();

// });

</script>

<script>
$("#print-btn").click(function () {

    var content = $(".result-table").prop('outerHTML');

    var printWindow = window.open('', '', 'width=1200,height=800');

    printWindow.document.write(`
        <html>
        <head>
            <title>Result Print</title>

            <style>

                @page{
                    size: A4 landscape;
                    margin:10mm;
                }

                body{
                    font-family: Arial;
                }

                table{
                    width:100%;
                    border-collapse: collapse;
                }

                th,td{
                    border:1px solid #000;
                    padding:6px;
                    text-align:center;
                    font-size : 12px;
                }
                .min-width{
                    width : 120px !important;
                }

                h4{
                    margin:2px;
                }

                .no-export{
                    display:none !important;
                }

                thead {
                    display: table-header-group;
                }

                tfoot {
                    display: table-footer-group;
                }

                .retotalling-subject-cell {
                    background-color: #e5e5e5 !important;
                }

            </style>

        </head>

        <body>

            ${content}

        </body>

        </html>
    `);

    printWindow.document.close();
    printWindow.focus();
    printWindow.print();

});
</script>


<script>
$("#print-nc-btn").click(function () {

    var content = $(".result-table").prop('outerHTML');

    var printWindow = window.open('', '', 'width=1200,height=800');

    printWindow.document.write(`
        <html>
        <head>
            <title>NC Course Print</title>

            <style>

                @page{
                    size: A4;
                    margin:10mm;
                }

                body{
                    font-family: Arial;
                }

                table{
                    width:100%;
                    border-collapse: collapse;
                }

                th,td{
                    border:1px solid #000;
                    padding:6px;
                    text-align:center;
                }

                h4{
                    margin:2px;
                }

                .no-export{
                    display:none !important;
                }

                #for-nc-hide{
                    display:none !important;
                }

            </style>

        </head>

        <body>

            ${content}

        </body>

        </html>
    `);

    printWindow.document.close();
    printWindow.focus();
    printWindow.print();

});
</script>

<script>
$("#dmc-print-btn").click(function () {
    var content = "";
    $(".dmc-result-table").each(function () {
        content += `
            <div class="dmc-page">
                <div class="dmc-print-wrapper">
                    <img
                        class="dmc-watermark"
                        src="${window.location.origin}/assets/images/watermark.png"
                        alt=""
                    >
                    ${$(this).prop("outerHTML")}
                </div>
            </div>
        `;
    });

    if (!content) {
        alert("No student result found.");
        return;
    }

    var printWindow = window.open("", "", "width=1200,height=800");

    printWindow.document.write(`
        <html>
        <head>
            <title>DMC Result Sheet Print</title>

            <style>
                @page {
                    size: A4;
                    margin: 10mm;
                }

                * {
                    box-sizing: border-box;
                }

                html,
                body {
                    margin: 0;
                    padding: 0;
                }

                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.4;
                }

                .dmc-page {
                    position: relative;
                    width: 100%;
                    min-height: calc(297mm - 20mm);
                    page-break-after: always;
                    break-after: page;
                }

                .dmc-page:last-child {
                    page-break-after: auto;
                    break-after: auto;
                }

                .dmc-print-wrapper {
                    position: relative;
                    width: 100%;
                    min-height: calc(297mm - 20mm);
                }

                .dmc-watermark {
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    width: 500px;
                    height: auto;
                    transform: translate(-50%, -50%);
                    opacity: 0.15;
                    pointer-events: none;
                    z-index: 0;
                }

                table.dmc-table {
                    position: relative;
                    width: 100%;
                    border: 0 !important;
                    border-collapse: collapse;
                    font-size: 13.5px;
                    z-index: 1;
                    page-break-inside: avoid;
                }

                table.dmc-table thead {
                    display: table-header-group;
                }

                table.dmc-table tfoot {
                    display: table-footer-group;
                }

                table.dmc-table thead tr td {
                    font-weight: bold;
                }

                table.dmc-table thead .header_info {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    column-gap: 0px;
                    margin-top: 14px;
                }

                table.dmc-table thead .header_info .personal-detail {
                    display: grid;
                    grid-template-columns: 130px 1fr;
                    align-items: center;
                    width: 100%;
                    font-weight: inherit;
                }

                table.dmc-table thead .header_info .personal-detail span {
                    font-weight: normal;
                    white-space: nowrap;
                }

                table.dmc-table thead .header_info .personal-detail b {
                    font-weight: 600;
                    white-space: nowrap;
                }

                table.dmc-table tbody {
                    border-bottom: 1px solid #000 !important;
                }

                table.dmc-table tbody tr {
                    page-break-inside: avoid;
                    break-inside: avoid;
                }

                table.dmc-table tbody tr td {
                    border: none !important;
                    padding-top: 8px !important;
                    padding-bottom: 8px !important;
                }

                table.dmc-table tfoot tr td {
                    border: none !important;
                    padding: 10px 6px !important;
                    line-height: 1.6;
                }

                table.dmc-table tfoot tr.bottom-signature td {
                    padding-top: 120px !important;
                }

                table.dmc-table tfoot tr.bottom-signature td.text-right {
                    text-align: right;
                }

                tr.top-bottom-border {
                    border-top: 1px solid #000 !important;
                    border-bottom: 1px solid #000 !important;
                }

                tr.top-bottom-border td {
                    padding: 8px 0 !important;
                }

                tr td.spacing {
                    padding: 12px 0 !important;
                }

                .no-export {
                    display: none !important;
                }
                td.text-end {
                    text-align: right !important;
                }

                @media print {
                    html,
                    body {
                        width: 210mm;
                        min-height: 297mm;
                    }

                    .dmc-page {
                        width: 100%;
                        min-height: calc(297mm - 20mm);
                        page-break-after: always;
                        break-after: page;
                    }

                    .dmc-page:last-child {
                        page-break-after: auto;
                        break-after: auto;
                    }

                    .dmc-watermark {
                        display: block !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }

                    body {
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                }
            </style>
        </head>

        <body>
            ${content}
        </body>
        </html>
    `);

    printWindow.document.close();

    var images = printWindow.document.querySelectorAll(".dmc-watermark");
    var loadedImages = 0;

    function startPrint() {
        if (loadedImages === images.length) {
            setTimeout(function () {
                printWindow.focus();
                printWindow.print();
            }, 300);
        }
    }

    if (images.length === 0) {
        startPrint();
    } else {
        images.forEach(function (image) {
            if (image.complete) {
                loadedImages++;
                startPrint();
            } else {
                image.onload = function () {
                    loadedImages++;
                    startPrint();
                };

                image.onerror = function () {
                    loadedImages++;
                    startPrint();
                };
            }
        });
    }
});
</script>

<script>
$("#compartment-print-btn").on("click", function (e) {

    e.preventDefault();

    /*
    |--------------------------------------------------------------------------
    | FIND ALL RESULT SHEETS
    |--------------------------------------------------------------------------
    | One .compartment-sheet = One Student = One A4 Page
    */
    var sheets = $(".compartment-sheet");

    if (sheets.length === 0) {
        alert("No student result found.");
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | WATERMARK URL
    |--------------------------------------------------------------------------
    */
    var watermarkUrl =
        window.location.origin + "/assets/images/watermark.png";


    /*
    |--------------------------------------------------------------------------
    | BUILD PRINT CONTENT
    |--------------------------------------------------------------------------
    */
    var content = "";

    sheets.each(function (index) {

        /*
        | Clone complete student sheet.
        |
        | This includes:
        | - Header
        | - Student details
        | - Result table
        | - Footer
        | - Existing watermark
        | - College logo
        */
        var sheet = $(this).clone();

        /*
        |--------------------------------------------------------------------------
        | REMOVE RESPONSIVE WRAPPER EFFECT
        |--------------------------------------------------------------------------
        | We are NOT changing the original Blade structure.
        | Only print-window styling handles it.
        */
        sheet.removeAttr("style");


        /*
        |--------------------------------------------------------------------------
        | MAKE SURE WATERMARK EXISTS
        |--------------------------------------------------------------------------
        |
        | Your Blade already contains:
        |
        | .compartment-watermark
        |
        | So normally this will already exist.
        |
        | This fallback guarantees that every printed sheet
        | has a watermark even if the watermark element is
        | missing from any particular result.
        */
        if (sheet.find(".compartment-watermark").length === 0) {

            sheet.prepend(`
                <div class="compartment-watermark">
                    <img
                        src="${watermarkUrl}"
                        alt="Watermark"
                    >
                </div>
            `);
        }


        /*
        |--------------------------------------------------------------------------
        | FIX WATERMARK IMAGE URL
        |--------------------------------------------------------------------------
        | Convert Blade generated /relative URLs into absolute URLs.
        */
        sheet.find(".compartment-watermark img").each(function () {
            $(this).attr("src", watermarkUrl);
        });


        /*
        |--------------------------------------------------------------------------
        | KEEP COLLEGE LOGO / OTHER IMAGES WORKING
        |--------------------------------------------------------------------------
        | Convert relative image paths into absolute paths.
        */
        sheet.find("img").each(function () {

            var src = $(this).attr("src");

            if (!src) {
                return;
            }

            /*
            | Ignore data URLs and already absolute URLs.
            */
            if (
                src.indexOf("data:") === 0 ||
                src.indexOf("http://") === 0 ||
                src.indexOf("https://") === 0
            ) {
                return;
            }

            /*
            | Convert relative URL to absolute URL.
            */
            if (src.charAt(0) === "/") {
                $(this).attr(
                    "src",
                    window.location.origin + src
                );
            } else {
                $(this).attr(
                    "src",
                    new URL(src, window.location.href).href
                );
            }
        });


        /*
        |--------------------------------------------------------------------------
        | ONE STUDENT = ONE PRINT PAGE
        |--------------------------------------------------------------------------
        */
        content += `
            <div class="dmc-page">

                <div class="dmc-print-wrapper">

                    ${sheet.prop("outerHTML")}

                </div>

            </div>
        `;
    });


    /*
    |--------------------------------------------------------------------------
    | OPEN PRINT WINDOW
    |--------------------------------------------------------------------------
    */
    var printWindow = window.open(
        "",
        "_blank",
        "width=1200,height=800"
    );


    if (!printWindow) {
        alert(
            "Print window was blocked by your browser. " +
            "Please allow pop-ups for this website."
        );
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | PRINT WINDOW HTML
    |--------------------------------------------------------------------------
    */
    printWindow.document.open();

    printWindow.document.write(`
        <!DOCTYPE html>

        <html>

        <head>

            <meta charset="UTF-8">

            <meta
                name="viewport"
                content="width=device-width, initial-scale=1.0"
            >

            <title>DMC Result Sheet Print</title>


            <style>

                /*
                |--------------------------------------------------------------------------
                | A4 PAGE
                |--------------------------------------------------------------------------
                */

                @page {
                    size: A4;
                    margin: 10mm;
                }


                /*
                |--------------------------------------------------------------------------
                | GLOBAL
                |--------------------------------------------------------------------------
                */

                * {
                    box-sizing: border-box;
                }


                html,
                body {
                    margin: 0;
                    padding: 0;
                }


                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.4;
                    background: #fff;
                }


                /*
                |--------------------------------------------------------------------------
                | ONE PAGE WRAPPER
                |--------------------------------------------------------------------------
                */

                .dmc-page {
                    position: relative;

                    width: 100%;

                    min-height: calc(297mm - 20mm);

                    page-break-after: always;
                    break-after: page;

                    overflow: hidden;
                }


                /*
                |--------------------------------------------------------------------------
                | LAST PAGE
                |--------------------------------------------------------------------------
                */

                .dmc-page:last-child {
                    page-break-after: auto;
                    break-after: auto;
                }


                /*
                |--------------------------------------------------------------------------
                | PRINT WRAPPER
                |--------------------------------------------------------------------------
                */

                .dmc-print-wrapper {
                    position: relative;

                    width: 100%;

                    min-height: calc(297mm - 20mm);

                    overflow: hidden;
                }


                /*
                |--------------------------------------------------------------------------
                | ORIGINAL BLADE SHEET
                |--------------------------------------------------------------------------
                */

                .compartment-sheet {
                    width: 100%;

                    position: relative;

                    overflow: hidden;
                }


                /*
                |--------------------------------------------------------------------------
                | WATERMARK
                |--------------------------------------------------------------------------
                |
                | IMPORTANT:
                | Every cloned student sheet contains its own
                | .compartment-watermark.
                |
                | Therefore every printed page gets its own watermark.
                */

                .compartment-watermark {
                    position: absolute;

                    top: 44%;
                    left: 50%;

                    width: 480px;
                    height: 480px;

                    transform: translate(-50%, -50%);

                    background-image: url("${watermarkUrl}");

                    background-repeat: no-repeat;

                    background-position: center center;

                    background-size: contain;

                    opacity: 0.25;

                    pointer-events: none;

                    z-index: 0;
                }


                .compartment-watermark img {
                    width: 100%;
                    height: 100%;

                    object-fit: contain;

                    display: block;
                }


                /*
                |--------------------------------------------------------------------------
                | RESULT TABLE
                |--------------------------------------------------------------------------
                */

                table.compartment-table {
                    position: relative;

                    width: 100%;

                    border: 0;

                    border-collapse: collapse;

                    font-size: 14px;

                    z-index: 1;
                }


                table.compartment-table thead,
                table.compartment-table tbody,
                table.compartment-table tfoot {
                    position: relative;

                    z-index: 2;
                }


                /*
                |--------------------------------------------------------------------------
                | HEADER
                |--------------------------------------------------------------------------
                */

                table.compartment-table thead tr td,
                table.compartment-table thead tr th {
                    //font-weight: bold;

                    border: 1px solid #000 !important;

                    border-collapse: collapse;

                    font-size: 11.8px;

                    text-align: center;
                    padding: 5px !important;
                    vertical-align: middle !important;
                }

                p{
                    margin : 0px;
                }


                /*
                |--------------------------------------------------------------------------
                | NO BORDER
                |--------------------------------------------------------------------------
                */

                table.compartment-table thead tr.no-border,
                table.compartment-table thead tr.no-border td,
                table.compartment-table thead tr.no-border th,
                table.compartment-table .no-border {
                    border: none !important;
                }


                /* ================================
                   STUDENT PERSONAL DETAILS
                ================================= */
                table.compartment-table thead .header_info {
                    display: grid;
                    grid-template-columns: 55% 45%;
                    width: calc(100% - 40px);
                    margin: 8px 20px 0 28px;
                    padding: 0;
                    align-items: center;
                    column-gap: 0;
                    row-gap: 8px;
                    box-sizing: border-box;
                }

                table.compartment-table thead .header_info .personal-detail {
                    display: grid;
                    grid-template-columns: 110px minmax(0, 1fr);
                    align-items: center;
                    width: 100%;
                    margin: 0;
                    padding: 0;
                    font-size: 11.3px;
                    font-weight: 400;
                    line-height: 1.4;
                    text-align: left;
                    color: #000;
                    box-sizing: border-box;
                }

                table.compartment-table thead .header_info .personal-detail span {
                    display: block;
                    width: auto;
                    white-space: nowrap;
                    font-weight: 400;
                    color: #000;
                }

                table.compartment-table thead .header_info .personal-detail b {
                    display: block;
                    min-width: 0;
                    font-size: 12px;
                    font-weight: 600;
                    color: #000;
                    overflow-wrap: anywhere;
                }

                /* Print layout */
                @media print {
                    table.compartment-table thead .header_info {
                        grid-template-columns: 55% 45%;
                        width: calc(100% - 40px);
                        margin: 8px 20px 0 28px;
                        column-gap: 0;
                        row-gap: 8px;
                    }

                    table.compartment-table thead .header_info .personal-detail {
                        grid-template-columns: 110px minmax(0, 1fr);
                        font-size: 14px;
                    }

                    table.compartment-table thead .header_info .personal-detail b {
                        font-size: 14px;
                    }
                }



                /*
                |--------------------------------------------------------------------------
                | BODY
                |--------------------------------------------------------------------------
                */

                table.compartment-table tbody {
                    border: 1px solid #000 !important;
                }


                table.compartment-table tbody tr td {
                    border: 1px solid #000 !important;

                    text-align: center;

                    vertical-align: middle !important;
                    padding: 5px !important;
                    font-size: 11px;
                }


                /*
                |--------------------------------------------------------------------------
                | FOOTER
                |--------------------------------------------------------------------------
                */

                table.compartment-table tfoot tr td {
                    border: none !important;
                    font-size : 12px;
                    vertical-align: middle !important;
                }


                table.compartment-table tfoot tr.bottom-signature td {
                    padding: 60px 30px 0px 30px;
                }


                /*
                |--------------------------------------------------------------------------
                | TEXT
                |--------------------------------------------------------------------------
                */

                .text-left {
                    text-align: left !important;
                }


                table.compartment-table tfoot tr.bottom-signature td.text-right {
                    text-align: right !important;
                }


                /*
                |--------------------------------------------------------------------------
                | TOP / BOTTOM BORDER
                |--------------------------------------------------------------------------
                */

                tr.top-bottom-border {
                    border: 1px solid #000 !important;

                    text-align: center;

                    vertical-align: middle !important;
                }


                tr.top-bottom-border td,
                tr.top-bottom-border th {
                    border: 1px solid #000 !important;

                    text-align: center;

                    vertical-align: middle !important;
                }


                /*
                |--------------------------------------------------------------------------
                | FLEX
                |--------------------------------------------------------------------------
                */

                .flex-between {
                    display: flex;

                    justify-content: space-between;
                }


                /*
                |--------------------------------------------------------------------------
                | COLLEGE LOGO
                |--------------------------------------------------------------------------
                */

                .clg-logo {
                    width: 80px;

                    height: auto;

                    position: relative;

                    top: 0;

                    left: 0;

                    //margin: 10px auto;
                }

                /*
                |--------------------------------------------------------------------------
                | MARGIN
                |--------------------------------------------------------------------------
                */

                .margin-b25 {
                    margin-bottom: 25px;
                    margin-top : 5px;
                }


                /*
                |--------------------------------------------------------------------------
                | RESPONSIVE TABLE
                |--------------------------------------------------------------------------
                |
                | Your Blade has:
                |
                | .compartment-sheet.table-responsive
                |
                | We keep the structure untouched but disable
                | horizontal scrolling during printing.
                */

                .table-responsive {
                    overflow: visible !important;
                }
                .font-40{
                    font-size : 14px;
                }

                .flex-between p span u {
                    text-underline-offset: 5px;
                }


                /*
                |--------------------------------------------------------------------------
                | PRINT
                |--------------------------------------------------------------------------
                */

                @media print {

                    html,
                    body {
                        width: 188mm;

                        min-height: 297mm;

                        margin: 0;

                        padding: 0;
                    }


                    .dmc-page {
                        width: 100%;

                        min-height: calc(297mm - 20mm);

                        page-break-after: always;

                        break-after: page;

                        overflow: hidden;
                    }


                    .dmc-page:last-child {
                        page-break-after: auto;

                        break-after: auto;
                    }


                    /*
                    | IMPORTANT:
                    | Preserve watermark during printing.
                    */

                    .compartment-watermark {
                        display: block !important;

                        visibility: visible !important;

                        opacity: 0.20 !important;

                        -webkit-print-color-adjust: exact !important;

                        print-color-adjust: exact !important;
                    }


                    .compartment-watermark img {
                        display: block !important;

                        visibility: visible !important;
                    }


                    body {
                        -webkit-print-color-adjust: exact !important;

                        print-color-adjust: exact !important;
                    }


                    /*
                    | Prevent a single student sheet from
                    | accidentally being split.
                    */

                    .compartment-sheet {
                        page-break-inside: avoid;

                        break-inside: avoid;
                    }


                    table.compartment-table {
                        page-break-inside: avoid;

                        break-inside: avoid;
                    }
                }

            </style>

        </head>


        <body>

            ${content}

        </body>

        </html>
    `);

    printWindow.document.close();


    /*
    |--------------------------------------------------------------------------
    | WAIT FOR ALL IMAGES
    |--------------------------------------------------------------------------
    |
    | We wait for:
    |
    | - Watermark
    | - College logo
    | - Any other image inside result sheet
    |
    | Only after ALL images are loaded print() will execute.
    |--------------------------------------------------------------------------
    */

    var images = printWindow.document.images;

    var totalImages = images.length;

    var loadedImages = 0;

    var printStarted = false;


    function startPrint() {

        if (printStarted) {
            return;
        }

        if (loadedImages >= totalImages) {

            printStarted = true;

            /*
            | Small delay ensures browser finishes
            | rendering the cloned tables/backgrounds.
            */

            setTimeout(function () {

                printWindow.focus();

                printWindow.print();

            }, 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | NO IMAGE CASE
    |--------------------------------------------------------------------------
    */

    if (totalImages === 0) {

        startPrint();

    } else {

        for (var i = 0; i < totalImages; i++) {

            var image = images[i];


            /*
            | Already loaded
            */

            if (image.complete) {

                loadedImages++;

            } else {

                /*
                | Successfully loaded
                */

                image.onload = function () {

                    loadedImages++;

                    startPrint();

                };


                /*
                | Even if one image fails,
                | don't stop printing all results.
                */

                image.onerror = function () {

                    loadedImages++;

                    startPrint();

                };
            }
        }


        /*
        | Check immediately after registering
        | all image handlers.
        */

        startPrint();
    }


    /*
    |--------------------------------------------------------------------------
    | OPTIONAL SAFETY TIMEOUT
    |--------------------------------------------------------------------------
    |
    | If a browser/network image gets stuck,
    | printing should still proceed.
    |--------------------------------------------------------------------------
    */

    setTimeout(function () {

        if (!printStarted) {

            printStarted = true;

            printWindow.focus();

            printWindow.print();
        }

    }, 5000);

});
</script>

<script>

$("#print-btn-a4").click(function () {

    var content = $(".result-table-a4").prop('outerHTML');

    var printWindow = window.open('', '', 'width=1200,height=800');

    printWindow.document.write(`
        <html>

        <head>

            <title>Result Print</title>

            <style>

                @page{
                    size:A4;
                    margin:10mm;
                }

                body{
                    font-family: Arial, sans-serif;
                    font-size:12px;
                    margin:0;
                    padding:0;
                }

                /* ================================
                   TABLE DESIGN
                ================================= */

                .result-table-a4{
                    width:100%;
                    border-collapse:collapse;
                }

                .result-table-a4 th,
                .result-table-a4 td{
                    border:1px solid #000;
                    padding:8px;
                    font-size:10px;
                    text-align:center;
                }

                /* Header style */

                .result-table-a4 thead th{
                    background:#f5f5f5;
                    font-weight:600;
                }

                /* Repeat header on every page */

                thead{
                    display:table-header-group;
                }

                /* Repeat footer on every page */

                tfoot{
                    display:table-footer-group;
                }

                /* Footer styling */

                .result-table-a4 tfoot td{
                    font-weight:600;
                    padding:25px 8px 10px;
                    text-align:center;
                    vertical-align:bottom;
                }

                .result-table-a4 tfoot td::before{
                    content:"";
                    display:block;
                    height:40px;
                }

                td.align-left{
                    text-align: left !important;
                }
                td.align-right{
                    text-align: right !important;
                }

                /* Avoid row breaking */

                tr{
                    page-break-inside:avoid;
                }

                /* Header section */

                .result-header h4{
                    margin:2px 0;
                }

                .result-header p{
                    margin:4px 0;
                    text-align : left;
                }

                /* Hide elements */

                .no-export{
                    display:none !important;
                }

                .course-info{
                    display:flex;
                    justify-content:space-between;
                    margin-bottom:6px;
                    font-size:12px;
                }
                h1,h2,h3,h4,h5,h6{
                    margin : 4px;
                }

            </style>

        </head>

        <body>

            ${content}

        </body>

        </html>
    `);

    printWindow.document.close();
    printWindow.focus();
    printWindow.print();

});

</script>


<script>

$("#print-cp-sheet-btn-a4").click(function () {

    /*
    |--------------------------------------------------------------------------
    | GET ALL CP SHEETS
    |--------------------------------------------------------------------------
    |
    | .prop('outerHTML') directly use karne par sirf first element milta hai.
    | Isliye each .cp-print-page ka outerHTML loop karke collect kar rahe hain.
    |
    */

    var content = "";

    $(".cp-print-page").each(function () {

        content += $(this).prop('outerHTML');

    });


    /*
    |--------------------------------------------------------------------------
    | CHECK DATA
    |--------------------------------------------------------------------------
    */

    if (!content || content.trim() === "") {

        alert("No CP Sheet data found.");

        return;

    }


    /*
    |--------------------------------------------------------------------------
    | OPEN PRINT WINDOW
    |--------------------------------------------------------------------------
    */

    var printWindow = window.open(
        '',
        '',
        'width=1200,height=800'
    );


    /*
    |--------------------------------------------------------------------------
    | CHECK POPUP BLOCKED
    |--------------------------------------------------------------------------
    */

    if (!printWindow) {

        alert(
            "Print window was blocked by your browser. Please allow pop-ups for this website."
        );

        return;

    }


    /*
    |--------------------------------------------------------------------------
    | PRINT HTML
    |--------------------------------------------------------------------------
    */

    printWindow.document.write(`

        <html>

        <head>

            <title>Result CP Sheet</title>

            <style>

                @page {

                    size: A4;

                    margin: 10mm;

                }


                html,
                body {

                    margin: 0;

                    padding: 0;

                    width: 100%;

                    font-family: Arial, sans-serif;

                    font-size: 12px;

                }


                body {

                    margin: 0;

                    padding: 0;

                }


                /*
                |--------------------------------------------------------------------------
                | CP PRINT PAGE
                |--------------------------------------------------------------------------
                */

                .cp-print-page {

                    width: 100%;

                    box-sizing: border-box;

                    page-break-after: always;

                    break-after: page;

                    page-break-inside: avoid;

                    break-inside: avoid;

                }


                .cp-print-page:last-child {

                    page-break-after: auto;

                    break-after: auto;

                }


                /*
                |--------------------------------------------------------------------------
                | TABLE
                |--------------------------------------------------------------------------
                */

                table {

                    width: 100%;

                    border: 1px solid #000;

                    border-collapse: collapse;

                    page-break-inside: avoid;

                    break-inside: avoid;

                }


                /*
                |--------------------------------------------------------------------------
                | TEXT ALIGNMENT
                |--------------------------------------------------------------------------
                */

                .text-right {

                    text-align: right;

                }


                .text-left {

                    text-align: left;

                }


                /*
                |--------------------------------------------------------------------------
                | HEADER TABLE
                |--------------------------------------------------------------------------
                */

                table.cp-header-table {

                    border-bottom: none;

                }


                /*
                |--------------------------------------------------------------------------
                | FOOTER TABLE
                |--------------------------------------------------------------------------
                */

                table.cp-footer-table {

                    border-top: none;

                }


                /*
                |--------------------------------------------------------------------------
                | COURSE TABLE
                |--------------------------------------------------------------------------
                */

                table.cp-course-table {

                    page-break-inside: auto;

                    break-inside: auto;

                }


                table.cp-course-table thead {

                    border: 1px solid #000 !important;

                    padding: 5px;

                }


                /*
                |--------------------------------------------------------------------------
                | UNIVERSITY HEADING
                |--------------------------------------------------------------------------
                */

                table.cp-header-table tr td h4 {

                    text-align: center;

                    font-size: 16px;

                    font-weight: bold;

                    margin-bottom: 20px;

                    margin-top: 20px;

                }


                /*
                |--------------------------------------------------------------------------
                | TABLE CELL PADDING
                |--------------------------------------------------------------------------
                */

                table.cp-header-table tr td,

                table.cp-course-table tr td,

                table.cp-course-table tr th,

                table.cp-footer-table tr td {

                    padding: 5px;

                }


                /*
                |--------------------------------------------------------------------------
                | FOOTER REGISTRAR SPACE
                |--------------------------------------------------------------------------
                */

                table.cp-footer-table tr.margin-90 td {

                    padding-top: 90px !important;

                }


                /*
                |--------------------------------------------------------------------------
                | PRINT SETTINGS
                |--------------------------------------------------------------------------
                */

                @media print {

                    html,
                    body {

                        width: 100%;

                        margin: 0;

                        padding: 0;

                    }


                    .cp-print-page {

                        width: 100%;

                        page-break-after: always;

                        break-after: page;

                        page-break-inside: avoid;

                        break-inside: avoid;

                    }


                    .cp-print-page:last-child {

                        page-break-after: auto;

                        break-after: auto;

                    }


                    /*
                    |--------------------------------------------------------------
                    | Prevent unnecessary blank page
                    |--------------------------------------------------------------
                    */

                    table {

                        page-break-inside: avoid;

                        break-inside: avoid;

                    }


                    /*
                    |--------------------------------------------------------------
                    | Course table can grow if many subjects exist
                    |--------------------------------------------------------------
                    */

                    table.cp-course-table {

                        page-break-inside: auto;

                        break-inside: auto;

                    }

                }

            </style>

        </head>


        <body>

            ${content}

        </body>

        </html>

    `);


    /*
    |--------------------------------------------------------------------------
    | CLOSE DOCUMENT
    |--------------------------------------------------------------------------
    */

    printWindow.document.close();


    /*
    |--------------------------------------------------------------------------
    | WAIT FOR CONTENT
    |--------------------------------------------------------------------------
    |
    | Important:
    | Browser ko complete HTML render karne ka time dete hain.
    |
    */

    printWindow.onload = function () {

        setTimeout(function () {

            printWindow.focus();

            printWindow.print();

        }, 800);

    };


    /*
    |--------------------------------------------------------------------------
    | FALLBACK
    |--------------------------------------------------------------------------
    |
    | Kuch browsers me onload already fire ho sakta hai.
    | Isliye extra timeout fallback rakha gaya hai.
    |
    */

    setTimeout(function () {

        if (!printWindow.closed) {

            printWindow.focus();

        }

    }, 1000);

});

</script>

<script>

$("#export-excel-btn").click(function(){

    let table = document.querySelector(".result-table");

    // store removed elements
    let removed = [];

    document.querySelectorAll(".no-export").forEach(el => {

        removed.push({
            element: el,
            parent: el.parentNode,
            next: el.nextSibling
        });

        el.parentNode.removeChild(el);

    });

    let workbook = XLSX.utils.table_to_book(table,{
        sheet:"Result"
    });

    let worksheet = workbook.Sheets["Result"];

    Object.keys(worksheet).forEach(function(cell){

        if(cell[0] === "!") return;

        if(!worksheet[cell].s) worksheet[cell].s = {};

        worksheet[cell].s = {
            alignment:{ wrapText:true }
        };

    });

    XLSX.writeFile(workbook,"Internal_Marks_Result.xlsx");

    // restore elements
    removed.forEach(item => {

        if(item.next){
            item.parent.insertBefore(item.element,item.next);
        }else{
            item.parent.appendChild(item.element);
        }

    });

});
</script>

<script id="printScript">
document.getElementById("transcript-print-btn").addEventListener("click", function () {

    let printContents = document.querySelector(".tanscript-table").innerHTML;

    let newWindow = window.open('', '', 'width=900,height=700');

    newWindow.document.write(`
        <html>
        <head>
            <title>Transcript</title>

            <style>
                body {
                    margin: 0;
                    padding: 10px;
                    font-family: Arial, sans-serif;
                }

                .result-transcript{
                    width: 100%;
                    display: flex;
                    gap: 2px;
                }
                .result-transcript .half{
                    width: 50%;
                    padding: 10px;
                }
                .result-transcript table{
                    width: 100%;
                    border-collapse: collapse;
                    border: 1px solid #000;
                }
                table .horizontal-border{
                    border-top: 1px solid #000;
                    border-bottom: 1px solid #000;
                }
                table .horizontal-border th{
                    text-align: center;
                }
                table tr td, table tr th{
                    padding: 5px;
                    font-size: 12px;
                }

                .profile-image {
                    display: flex;
                    justify-content: flex-end;
                }

                .profile-image img {
                    width: 80px;
                    height: 80px;
                    margin-right: 20px;
                }

                .student-details table {
                    width: 100%;
                    border-collapse: collapse;
                }

                .transcript-footer table {
                    width: 100%;
                    border-collapse: collapse;
                }

                .transcript-footer table thead{
                    border-bottom: 1px solid #000;
                }

                .transcript-footer table tfoot .right-text{
                    text-align: right;
                }

                /* =========================
                   A4 PRINT SETTINGS
                ==========================*/
                @page {
                    size: A4;
                    margin: 10mm;
                }

                @media print {
                    body {
                        margin: 0;
                    }
                }
            </style>
        </head>

        <body>
            ${printContents}

            <script>
                window.onload = function () {
                    window.print();

                    // ✅ Proper close AFTER print (fix PDF issue)
                    window.onafterprint = function () {
                        window.close();
                    };
                };
            <\/script>
        </body>
        </html>
    `);

    newWindow.document.close();
});
</script>
