<div class="container py-4">
    <h3 class="mb-3">🏫 Welcome, {{ auth('institute')->user()->name ?? 'Institute' }}</h3>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>🏫 Institute Dashboard</h3>

        @if(auth('institute')->check())
            <form action="{{ route('institute.logout') }}" method="POST">
                @csrf
                <button class="btn btn-danger btn-sm">Logout</button>
            </form>
        @else
            <a href="{{ route('institute.login') }}" class="btn btn-primary btn-sm">Login</a>
        @endif
    </div>

    @if(auth('institute')->check())
        <h5 class="mb-3">Welcome, {{ auth('institute')->user()->name ?? 'Institute' }}</h5>

        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h6>Total Students</h6>
                        <h2>320</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h6>Total Branches</h6>
                        <h2>5</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h6>Pending Approvals</h6>
                        <h2>12</h2>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-warning">
            Please login to access institute dashboard.
        </div>
    @endif
</div>