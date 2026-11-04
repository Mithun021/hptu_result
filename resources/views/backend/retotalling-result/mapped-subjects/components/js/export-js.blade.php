<script>
    $(document).on('click', '#sheet-print-btn', function () {

        let printContent = document.getElementById('resultSheet');

        if (!printContent) {
            alert('Print sheet not found!');
            return;
        }

        let printWindow = window.open('', '_blank');

        if (!printWindow) {
            alert('Please allow pop-ups to print the sheet.');
            return;
        }

        let styles = '';

        // Copy existing CSS files and inline styles
        document.querySelectorAll('link[rel="stylesheet"], style').forEach(function (item) {
            styles += item.outerHTML;
        });

        printWindow.document.open();

        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Print Result Sheet</title>

                ${styles}

                <style>

                    /* A4 Page Settings */
                    @page {
                        size: A4 portrait;
                        margin: 8mm;
                    }

                    * {
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }

                    html,
                    body {
                        width: 100%;
                        margin: 0 !important;
                        padding: 0 !important;
                        background: #fff !important;
                    }

                    body {
                        font-family: Arial, sans-serif;
                    }

                    /* Hide No Export Elements */
                    .no-export {
                        display: none !important;
                    }

                    /* Main Sheet */
                    #resultSheet {
                        width: 100% !important;
                        max-width: 100% !important;
                        margin: 0 !important;
                        padding: 0 !important;
                        overflow: visible !important;
                    }

                    /* Table Settings */
                    table {
                        width: 100% !important;
                        border-collapse: collapse !important;
                        font-size: 10px !important;
                        margin-bottom: 5px !important;
                    }

                    /* Table Header */
                    thead th {
                        font-size: 10px !important;
                        padding: 4px 5px !important;
                        line-height: 1.2 !important;
                        vertical-align: middle !important;
                    }

                    /* Table Body */
                    tbody td {
                        font-size: 9px !important;
                        padding: 3px 5px !important;
                        line-height: 1.2 !important;
                        vertical-align: middle !important;
                    }

                    /* Inner Elements */
                    tbody td * {
                        font-size: 9px !important;
                        line-height: 1.2 !important;
                    }

                    /* Table Borders */
                    table th,
                    table td {
                        border-collapse: collapse !important;
                    }

                    /* Avoid Row Breaking */
                    tr,
                    td,
                    th {
                        page-break-inside: avoid !important;
                        break-inside: avoid !important;
                    }

                    /* Compact Lists */
                    .mapped-subject-list {
                        gap: 2px !important;
                    }

                    .mapped-subject-item {
                        font-size: 8px !important;
                        line-height: 1.1 !important;
                    }

                    /* Remove Unnecessary Spacing */
                    p {
                        margin-bottom: 3px !important;
                    }

                    /* Responsive Table */
                    .table-responsive {
                        overflow: visible !important;
                    }

                </style>
            </head>

            <body>

                ${printContent.innerHTML}

                <script>
                    window.onload = function () {

                        window.focus();

                        setTimeout(function () {
                            window.print();
                        }, 500);

                    };

                    window.onafterprint = function () {
                        window.close();
                    };
                <\/script>

            </body>
            </html>
        `);

        printWindow.document.close();

    });
</script>
