<!-- Left Sidebar Start -->
<div class="app-sidebar-menu">
    <div class="h-100" data-simplebar>

        <!--- Sidemenu -->
        <div id="sidebar-menu">

            <div class="logo-box">
                <a href="{{ route('admindashboard.get') }}" class="logo logo-light">
                    <span class="logo-sm">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="" height="22">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="" height="24">
                    </span>
                </a>
                <a href="{{ route('admindashboard.get') }}" class="logo logo-dark">
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
                    <a href="{{ route('admindashboard.get') }}" class="tp-link">
                        <i data-feather="home"></i>
                        <span> Dashboard </span>
                    </a>
                </li>

                <li class="menu-title">Academic</li>
                <li>
                    <a href="#master" data-bs-toggle="collapse">
                        <i data-feather="aperture"></i>
                        <span>Exam / Result Master </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="master">
                        <ul class="nav-second-level">
                            <li><a href="{{ route('exams.index') }}" class="tp-link">Exam Name</a></li>
                            <li><a href="{{ route('mid-sem-exams.index') }}" class="tp-link">Mid-Sem/Internal Exam Name</a></li>
                            <li><a href="{{ route('exam-fees.index') }}" class="tp-link">Exam Fees</a></li>
                            <li><a href="{{ route('exam-centers.index') }}" class="tp-link">Exam Centers</a></li>
                            <li><a href="{{ route('block-students.index') }}" class="tp-link">Blocked Students</a></li>
                            <li><a href="{{ route('pass-percentages.index') }}" class="tp-link">Pass Percentage</a></li>
                            <li><a href="{{ route('marks-grades.index') }}" class="tp-link">Marks Grade</a></li>
                            {{-- <li><a href="{{ route('grace-mark-policies.index') }}" class="tp-link">Grace Marks Policy</a></li> --}}
                        </ul>
                    </div>
                </li>

                    <li>
                    <a href="#result" data-bs-toggle="collapse">
                        <i data-feather="award"></i>
                        <span> Result Prepare </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="result">
                        <ul class="nav-second-level">
                            <li><a href="{{ route('results.internal-marks') }}" class="tp-link">Internal Marks</a></li>
                            <li><a href="{{ route('results.external-marks') }}" class="tp-link">External Marks</a></li>
                            <li><a href="{{ route('results.result-prepare') }}" class="tp-link">Results</a></li>
                            <li>
                                <a href="#suppleResult" data-bs-toggle="collapse">
                                    <span>Supple/Compartment Result</span>
                                    <span class="menu-arrow"></span>
                                </a>

                                <div class="collapse" id="suppleResult">
                                    <ul class="nav-third-level">
                                        <li>
                                            <a href="{{ route('results.supple-mapped-subject') }}" class="tp-link">
                                                Mapped Subject
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('results.supple-result-prepare') }}" class="tp-link">
                                                Result Prepare
                                            </a>
                                        </li>
                                       <li>
                                            <a href="{{ route('results.supple.subject-wise-result') }}" class="tp-link">
                                                Subject Wise Result
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('results.supple.overall-result') }}" class="tp-link">
                                                Overall Result
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li>
                                <a href="#resultOverview" data-bs-toggle="collapse">
                                    <span>Result Overview</span>
                                    <span class="menu-arrow"></span>
                                </a>

                                <div class="collapse" id="resultOverview">
                                    <ul class="nav-third-level">
                                        <li>
                                            <a href="{{ route('results.overview') }}" class="tp-link">Endsem Results</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('results.supple-overview') }}" class="tp-link">Supple Result</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li>
                                <a href="#cpSheet" data-bs-toggle="collapse">
                                    <span>CP Sheet</span>
                                    <span class="menu-arrow"></span>
                                </a>

                                <div class="collapse" id="cpSheet">
                                    <ul class="nav-third-level">
                                        <li>
                                            <a href="{{ route('results.cp-sheet.end-sem') }}" class="tp-link">Endsem CP Sheet</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('results.cp-sheet.supple') }}" class="tp-link">Supple CP Sheet</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li>
                                <a href="#frontSheet" data-bs-toggle="collapse">
                                    <span>Front Sheet</span>
                                    <span class="menu-arrow"></span>
                                </a>

                                <div class="collapse" id="frontSheet">
                                    <ul class="nav-third-level">
                                        <li>
                                            <a href="{{ route('results.frontsheet.nc-course') }}" class="tp-link">NC Course</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('results.frontsheet.end-sem') }}" class="tp-link">Annual Prog.</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li><a href="{{ route('results.grace') }}" class="tp-link">Grace Marks</a></li>
                            <li><a href="{{ route('results.regular.dmc') }}" class="tp-link">DMC Sheet</a></li>
                            <li><a href="{{ route('results.transcript') }}" class="tp-link">Transcript</a></li>
                        </ul>
                    </div>
                </li>

                <li>
                    <a href="#re_totalling" data-bs-toggle="collapse">
                        <i data-feather="edit-3"></i>
                        <span> Re-Totalling Result </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="re_totalling">
                        <ul class="nav-second-level">
                            <li><a href="{{ route('retotal-result.mapped-subjects.index') }}" class="tp-link">Assign Subjects</a></li>
                            <li>
                                <a href="#retotal_regular" data-bs-toggle="collapse">
                                    <span>Regular Result</span>
                                    <span class="menu-arrow"></span>
                                </a>

                                <div class="collapse" id="retotal_regular">
                                    <ul class="nav-third-level">
                                        <li><a href="{{ route('retotal-result.regular.external-marks.index') }}" class="tp-link">External Marks</a></li>
                                        <li><a href="{{ route('retotal-result.regular.result-preview.subjectwise.index') }}" class="tp-link">Result Preview</a></li>
                                        {{-- <li><a href="{{ route('semesters.index') }}" class="tp-link">Retotalling Campair Sheet</a></li> --}}
                                    </ul>
                                </div>
                            </li>

                            {{-- <li>
                                <a href="#retotal_compartment" data-bs-toggle="collapse">
                                    <span>Compart./Supple</span>
                                    <span class="menu-arrow"></span>
                                </a>

                                <div class="collapse" id="retotal_compartment">
                                    <ul class="nav-third-level">
                                        <li><a href="{{ route('branches.index') }}" class="tp-link">External Marks</a></li>
                                        <li><a href="{{ route('schemes.index') }}" class="tp-link">Overall Performance</a></li>
                                        <li><a href="{{ route('semesters.index') }}" class="tp-link">Retotalling Campair Sheet</a></li>
                                    </ul>
                                </div>
                            </li> --}}

                        </ul>
                    </div>
                </li>

                <li>
                    <a href="#academic" data-bs-toggle="collapse">
                        <i data-feather="book-open"></i>
                        <span> Academic </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="academic">
                        <ul class="nav-second-level">
                            <li><a href="{{ route('courses.index') }}" class="tp-link">Courses</a></li>
                            <li><a href="{{ route('branches.index') }}" class="tp-link">Branch</a></li>
                            <li><a href="{{ route('schemes.index') }}" class="tp-link">Scheme</a></li>
                            <li><a href="{{ route('semesters.index') }}" class="tp-link">Semester</a></li>
                            {{-- <li><a href="{{ route('annual-semesters.index') }}" class="tp-link">Annual Semester</a></li> --}}
                            <li><a href="{{ route('sessions.index') }}" class="tp-link">Session</a></li>
                        </ul>
                    </div>
                </li>

                <li>
                    <a href="{{ route('subjects.index') }}" class="tp-link">
                        <i data-feather="book"></i>
                        <span> Subjects </span>
                    </a>
                </li>

                <li class="menu-title">Users</li>

                <li>
                    <a href="{{ route('institutes.index') }}" class="tp-link">
                        <i data-feather="home"></i>
                        <span> Institutes </span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('students.index') }}" class="tp-link">
                        <i data-feather="user"></i>
                        <span> Students </span>
                    </a>
                </li>

                <li>
                    <a href="#sidebarAuth" data-bs-toggle="collapse">
                        <i data-feather="users"></i>
                        <span> Employees/Faculty </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="sidebarAuth">
                        <ul class="nav-second-level">
                            <li>
                                <a href="{{ route('employee') }}" class="tp-link">Faculty/Employee List</a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="menu-title">System</li>

                <li>
                    <a href="{{ route('departments.index') }}" class="tp-link">
                        <i data-feather="aperture"></i>
                        <span> Department </span>
                    </a>
                </li>

                {{-- <li>
                    <a href="widgets.html" class="tp-link">
                        <i data-feather="aperture"></i>
                        <span> Widgets </span>
                    </a>
                </li> --}}

                <li>
                    <a href="#sidebarMaps" data-bs-toggle="collapse">
                        <i data-feather="settings"></i>
                        <span> Settings </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="sidebarMaps">
                        <ul class="nav-second-level">
                            <li>
                                <a href="{{ route('backend.mail-config.index') }}" class="tp-link">Mail Config</a>
                            </li>
                            <li>
                                <a href="{{ route('backend.send-mail.index') }}" class="tp-link">Send Mail</a>
                            </li>
                            <li>
                                <a href="{{ route('roles') }}" class="tp-link">Roles</a>
                            </li>
                            <li>
                                <a href="{{ route('permission') }}" class="tp-link">Permissions</a>
                            </li>
                            <li>
                                <a href="{{ route('permission-categories.index') }}" class="tp-link">Permissions Category</a>
                            </li>
                        </ul>
                    </div>
                </li>

            </ul>

        </div>
        <!-- End Sidebar -->

        <div class="clearfix"></div>

    </div>
</div>
<!-- Left Sidebar End -->
