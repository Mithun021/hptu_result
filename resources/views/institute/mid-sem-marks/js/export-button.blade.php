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
                }

                h4{
                    margin:2px;
                }

                .no-export{
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

    var content = $(".dmc-result-table").prop('outerHTML');

    var printWindow = window.open('', '', 'width=1200,height=800');

    printWindow.document.write(`
        <html>
        <head>
            <title>DMC Result Sheet Print</title>

            <style>

                @page{
                    size: A4;
                    margin:10mm;
                }

                body{
                    font-family: Arial;
                    line-height: 1.4;
                }
                table.dmc-table {
                    position: relative;
                    width: 100%;
                    border: 0px !important;
                    border-collapse: collapse;
                    width: 100%;
                    font-size: 13px;
                    z-index: 1;
                }

                /* ✅ WATERMARK FIXED */
                table.dmc-table::before{
                    content: "";
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;

                    background-image: url('${window.location.origin}/assets/images/watermark.png');
                    background-repeat: no-repeat;
                    background-position: center;
                    background-size: 500px;

                    opacity: 0.08;
                    pointer-events: none;
                    z-index: 0;
                }

                table.dmc-table thead tr td{
                    font-weight: bold;
                }

                table.dmc-table thead .header_info{
                    display: flex;
                    justify-content: space-between;
                    margin-top: 10px;
                }

                table.dmc-table thead .header_info .personal-detail{
                    width: 50%;
                    font-weight: inherit;
                }

                /* =========================
                   TBODY SPACING IMPROVED
                ========================= */
                table.dmc-table tbody{
                    border-bottom: 1px solid #000 !important;
                }

                table.dmc-table tbody tr td{
                    border: none !important;
                    padding: 8px 6px !important;   /* 🔥 more vertical space */
                    line-height: 1.6;
                }

                /* =========================
                   TFOOT SPACING IMPROVED
                ========================= */
                table.dmc-table tfoot tr td{
                    border: none !important;
                    padding: 10px 6px !important;  /* 🔥 footer spacing */
                    line-height: 1.6;
                }

                /* SIGNATURE SPACE */
                table.dmc-table tfoot tr.bottom-signature td{
                    padding-top: 120px !important; 
                }

                table.dmc-table tfoot tr.bottom-signature td.text-right{
                    text-align : right;
                }

                /* BORDER ROW */
                tr.top-bottom-border{
                    border-top: 1px solid #000 !important;
                    border-bottom: 1px solid #000 !important;
                }
                tr.top-bottom-border td{
                    padding: 8px 0px !important;
                }

                /* EXTRA SPACING ROW */
                tr td.spacing{
                    padding: 12px 0 !important;
                }

                .no-export{
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