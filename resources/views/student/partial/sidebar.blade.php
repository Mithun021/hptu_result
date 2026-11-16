<!-- Left Sidebar Start -->
            <div class="app-sidebar-menu">
                <div class="h-100" data-simplebar>

                    <!--- Sidemenu -->
                    <div id="sidebar-menu">

                        <div class="logo-box">
                            <a href="{{ route('student.dashboard') }}" class="logo logo-light">
                                <span class="logo-sm">
                                    <img src="{{ asset('assets/images/logo.png') }}" alt="" height="22">
                                </span>
                                <span class="logo-lg">
                                    <img src="{{ asset('assets/images/logo.png') }}" alt="" height="24">
                                </span>
                            </a>
                            <a href="{{ route('student.dashboard') }}" class="logo logo-dark">
                                <span class="logo-sm">
                                    <img src="{{ asset('assets/images/logo.png') }}" alt="" height="22">
                                </span>
                                <span class="logo-lg">
                                    <img src="{{ asset('assets/images/logo.png') }}" alt="" height="24">
                                </span>
                            </a>
                        </div>

                        <ul id="side-menu">

                            <li class="menu-title">Menu</li>

                            <li>
                                <a href="{{ route('student.dashboard') }}" class="tp-link">
                                    <i data-feather="home"></i>
                                    <span> Dashboard </span>
                                </a>
                            </li>

                            <li class="menu-title">Academic</li>
                            <li>
                                <a href="#examination" data-bs-toggle="collapse">
                                    <i data-feather="aperture"></i>
                                    <span>Examination Form </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="examination">
                                    <ul class="nav-second-level">
                                        <li><a href="{{ route('student.examination-form.index') }}" class="tp-link">Fill Examination Form</a></li>
                                        <li><a href="{{ route('pass-percentages.index') }}" class="tp-link">Form Records</a></li>
                                    </ul>
                                </div>
                            </li>

                            {{-- <li class="menu-title">Users</li> --}}

                            <li>
                                <a href="{{ route('student.verify-subjects.index') }}" class="tp-link">
                                    <i data-feather="clipboard"></i>
                                    <span> Verify Subjects </span>
                                </a>
                            </li>

                            {{-- <li>
                                <a href="widgets.html" class="tp-link">
                                    <i data-feather="aperture"></i>
                                    <span> Widgets </span>
                                </a>
                            </li> --}}

                        </ul>

                    </div>
                    <!-- End Sidebar -->

                    <div class="clearfix"></div>

                </div>
            </div>
            <!-- Left Sidebar End -->
