</div>
        <!-- END wrapper -->

        <!-- Vendor -->
        <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
        <script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
        <script src="{{ asset('assets/libs/waypoints/lib/jquery.waypoints.min.js') }}"></script>
        <script src="{{ asset('assets/libs/jquery.counterup/jquery.counterup.min.js') }}"></script>
        <script src="{{ asset('assets/libs/feather-icons/feather.min.js') }}"></script>

        <!-- Datatables js -->
        <script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>

        <!-- Required for HTML5 buttons -->
        <script src="{{ asset('assets/libs/jszip/jszip.min.js') }}"></script>
        <script src="{{ asset('assets/libs/pdfmake/pdfmake.min.js') }}"></script>
        <script src="{{ asset('assets/libs/pdfmake/vfs_fonts.js') }}"></script>

        <!-- dataTables.bootstrap5 -->
        <script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
        <script src="{{ asset('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>

        <!-- buttons.colVis -->
        <script src="{{ asset('assets/libs/datatables.net-buttons/js/buttons.colVis.min.js') }}"></script>
        <script src="{{ asset('assets/libs/datatables.net-buttons/js/buttons.flash.min.js') }}"></script>
        <script src="{{ asset('assets/libs/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>
        <script src="{{ asset('assets/libs/datatables.net-buttons/js/buttons.print.min.js') }}"></script>

        <!-- buttons.bootstrap5 -->
        <script src="{{ asset('assets/libs/datatables.net-buttons-bs5/js/buttons.bootstrap5.min.js') }}"></script>

        <!-- dataTables.keyTable -->
        <script src="{{ asset('assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js') }}"></script>
        <script src="{{ asset('assets/libs/datatables.net-keytable-bs5/js/keyTable.bootstrap5.min.js') }}"></script>

        <!-- dataTable.responsive -->
        <script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
        <script src="{{ asset('assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>

        <!-- dataTables.select -->
        <script src="{{ asset('assets/libs/datatables.net-select/js/dataTables.select.min.js') }}"></script>
        <script src="{{ asset('assets/libs/datatables.net-select-bs5/js/select.bootstrap5.min.js') }}"></script>

        <!-- Datatable Demo App Js -->
        <script src="{{ asset('assets/js/pages/datatable.init.js') }}"></script>

        <!-- App js-->
        <script src="{{ asset('assets/js/app.js') }}"></script>

        <script src="{{ asset('assets/libs/jspdf/jspdf.umd.min.js') }}"></script>
        <script src="{{ asset('assets/libs/sheetJs/xlsx.full.min.js') }}"></script>
        <script src="{{ asset('assets/js/datatable-global.js') }}"></script>
        <script src="{{ asset('assets/toastify/toastify-js') }}"></script>
        <script src="{{ asset('assets/libs/jquery-validate/jquery.validate.min.js') }}"></script>
        <script>

            /* Prevent Mouse Scroll from Changing Number Input */
            $(document).on("wheel", 'input[type="number"]', function () {
                $(this).blur();
            });

            /* Prevent Keyboard Up/Down Arrow from Changing Number Input */
            $(document).on("keydown", 'input[type="number"]', function (e) {

                if (e.key === "ArrowUp" || e.key === "ArrowDown") {
                    e.preventDefault();
                }

            });

            document.addEventListener("DOMContentLoaded", function () {

                @if (session('success'))
                    Toastify({
                        text: "{{ session('success') }}",
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        close: true,
                        backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
                        stopOnFocus: true
                    }).showToast();
                @endif

                @if (session('error'))
                    Toastify({
                        text: "{{ session('error') }}",
                        duration: 4000,
                        gravity: "top",
                        position: "right",
                        close: true,
                        backgroundColor: "linear-gradient(to right, #ff416c, #ff4b2b)",
                        stopOnFocus: true
                    }).showToast();
                @endif

                @if ($errors->any())

                    @foreach ($errors->all() as $error)

                        Toastify({
                            text: "{{ $error }}",
                            duration: 5000,
                            gravity: "top",
                            position: "right",
                            close: true,
                            backgroundColor: "linear-gradient(to right, #ff416c, #ff4b2b)",
                            stopOnFocus: true
                        }).showToast();

                    @endforeach

                @endif

            });
        </script>

        @stack('scripts')

    </body>
</html>
