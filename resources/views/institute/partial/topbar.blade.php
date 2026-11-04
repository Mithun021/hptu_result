<!-- Topbar Start -->
            <div class="topbar-custom">
                <div class="container-fluid">
                    <div class="d-flex justify-content-between">
                        <ul class="list-unstyled topnav-menu mb-0 d-flex align-items-center">
                            <li>
                                <button class="button-toggle-menu nav-link">
                                    <i data-feather="menu" class="noti-icon"></i>
                                </button>
                            </li>
                            <li class="d-none d-lg-block">
                                <h5 class="mb-0">Welcome, <span class="text-success">
                                    {{
                                        auth('institute')->user()->account_type === 'institute'
                                            ? auth('institute')->user()->name
                                            : auth('institute')->user()->parentInstitute?->name
                                    }}
                                    </span>
                                </h5>
                            </li>
                        </ul>

                        <ul class="list-unstyled topnav-menu mb-0 d-flex align-items-center">

                            <!-- Button Trigger Customizer Offcanvas -->
                            <li class="d-none d-sm-flex">
                                <button type="button" class="btn nav-link" data-toggle="fullscreen">
                                    <i data-feather="maximize" class="align-middle fullscreen noti-icon"></i>
                                </button>
                            </li>

                            <!-- Light/Dark Mode Button Themes -->
                            <li class="d-none d-sm-flex">
                                <button type="button" class="btn nav-link" id="light-dark-mode">
                                    <i data-feather="moon" class="align-middle dark-mode"></i>
                                    <i data-feather="sun" class="align-middle light-mode"></i>
                                </button>
                            </li>

                            <li class="dropdown notification-list topbar-dropdown">
                                <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                                    <i data-feather="bell" class="noti-icon"></i>
                                    <span class="badge bg-danger rounded-circle noti-icon-badge">9</span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end dropdown-lg">
                                    <!-- item-->
                                    <div class="dropdown-item noti-title">
                                        <h5 class="m-0">
                                            <span class="float-end"><a href="" class="text-dark"><small>Clear All</small></a></span>Notification
                                        </h5>
                                    </div>

                                    <div class="noti-scroll" data-simplebar>
                                        <!-- item-->
                                        <a href="javascript:void(0);"
                                            class="dropdown-item notify-item text-muted link-primary active">
                                            <div class="notify-icon">
                                                <img src="{{ asset('assets/images/users/user-12.jpg') }}" class="img-fluid rounded-circle" alt="" />
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <p class="notify-details">Carl Steadham</p>
                                                <small class="text-muted">5 min ago</small>
                                            </div>
                                            <p class="mb-0 user-msg">
                                                <small class="fs-14">Completed <span class="text-reset">Improve workflow in Figma</span></small>
                                            </p>
                                        </a>
                                    </div>

                                    <!-- All-->
                                    <a href="javascript:void(0);" class="dropdown-item text-center text-primary notify-item notify-all">View all
                                        <i class="fe-arrow-right"></i>
                                    </a>
                                </div>
                            </li>

                            <!-- User Dropdown -->
                            <li class="dropdown notification-list topbar-dropdown">
                                <a class="nav-link dropdown-toggle nav-user me-0" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                                    <img src="{{ asset('assets/images/hptu-logo.png') }}" alt="user-image" class="rounded-circle" />
                                    <span class="pro-user-name ms-1">{{ auth('institute')->user()->sort_name ?? 'Institute' }} <i class="mdi mdi-chevron-down"></i></span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end profile-dropdown">
                                    <!-- item-->
                                    <div class="dropdown-header noti-title">
                                        <h6 class="text-overflow m-0">Welcome !</h6>
                                    </div>

                                    <!-- item-->
                                    <a href="pages-profile.html" class="dropdown-item notify-item">
                                        <i class="mdi mdi-account-circle-outline fs-16 align-middle"></i>
                                        <span>My Account</span>
                                    </a>
                                    <div class="dropdown-divider"></div>

                                    <!-- item-->
                                    <a href="#" class="dropdown-item notify-item"
                                    onclick="event.preventDefault(); document.getElementById('student-logout-form').submit();">
                                        <i class="mdi mdi-location-exit fs-16 align-middle"></i>
                                        <span>Logout</span>
                                    </a>

                                    <form id="student-logout-form" action="{{ route('institute.logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- end Topbar -->
