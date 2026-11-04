<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>🎓 Student Dashboard</h3>
         <h3 class="mb-3">👋 Welcome, {{ auth('student')->user()->student_name ?? 'Student' }}</h3>
        @if(auth('student')->check())
            <form action="{{ route('student.logout') }}" method="POST">
                @csrf
                <button class="btn btn-danger btn-sm">Logout</button>
            </form>
        @else
            <a href="{{ route('student.login') }}" class="btn btn-primary btn-sm">Login</a>
        @endif
    </div>

    @if(auth('student')->check())
        <h5 class="mb-3">Welcome, {{ auth('student')->user()->student_name ?? 'Student' }}</h5>

        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h6>Total Subjects</h6>
                        <h2>6</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h6>Current Semester</h6>
                        <h2>Semester 4</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h6>Result Status</h6>
                        <h2>Pending</h2>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-warning">
            Please login to view your dashboard.
        </div>
    @endif
</div>