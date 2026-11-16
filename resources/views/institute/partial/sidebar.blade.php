@php
    $authUser = auth('institute')->user();

    $instituteId = $authUser->account_type === 'institute'
        ? auth('institute')->id()
        : $authUser->institute_id;
@endphp

<!-- Left Sidebar Start -->
            <div class="app-sidebar-menu">
                <div class="h-100" data-simplebar>

                    <!--- Sidemenu -->
                    <div id="sidebar-menu">

                        <div class="logo-box">
                            <a href="{{ route('institute.dashboard') }}" class="logo logo-light">
                                <span class="logo-sm">
                                    <img src="{{ asset('assets/images/logo-sm.png') }}" alt="" height="22">
                                </span>
                                <span class="logo-lg">
                                    <img src="{{ asset('assets/images/logo-light.png') }}" alt="" height="24">
                                </span>
                            </a>
                            <a href="{{ route('institute.dashboard') }}" class="logo logo-dark">
                                <span class="logo-sm">
                                    <img src="{{ asset('assets/images/logo-sm.png') }}" alt="" height="22">
                                </span>
                                <span class="logo-lg">
                                    <img src="{{ asset('assets/images/logo-dark.png') }}" alt="" height="24">
                                </span>
                            </a>
                        </div>

                        <ul id="side-menu">

                            <li class="menu-title">Menu</li>

                            <li>
                                <a href="{{ route('institute.dashboard') }}" class="tp-link">
                                    <i data-feather="home"></i>
                                    <span> Dashboard </span>
                                </a>
                            </li>

                            <li class="menu-title">Academic</li>
                            <li>
                                <a href="#result" data-bs-toggle="collapse">
                                    <i data-feather="aperture"></i>
                                    <span>Result </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="result">
                                    <ul class="nav-second-level">
                                        <li><a href="{{ route('marks.mid-sem') }}" class="tp-link">Prepare Internal Marks</a></li>
                                        {{-- <li><a href="#" class="tp-link">Complete Internal Marks</a></li>
                                        <li><a href="#" class="tp-link">External marks</a></li> --}}
                                    </ul>
                                </div>
                            </li>

                            <li class="menu-title">Users</li>
                            @if($authUser->account_type === 'institute')
                            <li>
                                <a href="{{ route('teachers.index') }}" class="tp-link">
                                    <i data-feather="user"></i>
                                    <span> Teachers </span>
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('assign.subject') }}" class="tp-link">
                                    <i data-feather="book"></i>
                                    <span> Assign Subject </span>
                                </a>
                            </li>
                            @endif

                            <li>
                                <a href="{{ route('institute.students.index') }}" class="tp-link">
                                    <i data-feather="users"></i>
                                    <span> Students </span>
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('student-subject.index') }}" class="tp-link">
                                    <i data-feather="user"></i>
                                    <span> Student Subjects </span>
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