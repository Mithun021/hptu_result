@extends('backend.partial.master')
@section('title', 'Session')
@section('sub_title', 'Session List')
@section('backend-content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h4>Add New Session</h4>
            </div>
            <form action="{{ route('sessions.store') }}" method="post">
                @csrf
            <div class="card-body px-1">
                <div class="form-group mb-3">
                    <label for="">Session Name<span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                    @error('name')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="card-body">
                <button type="submit" class="btn btn-primary">Add Session</button>
            </div>
            </form>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4>Session List</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive database-table">
                    <table class="table table-bordered table-bordered"  id="responsive-datatable">
                        <thead class="table-primary">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th scope="col" id="no-export">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($sessions as $index => $item)
                            <tr>
                                <td>{{ ++$index }}</td>
                                <td>{{ $item->name }}</td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-transparent dropdown-toggle" type="button" 
                                                id="actionDropdown{{ $item->id }}" data-bs-toggle="dropdown" 
                                                aria-expanded="false">
                                            <i class="mdi mdi-dots-vertical fs-16"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-start" aria-labelledby="actionDropdown{{ $item->id }}">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('sessions.edit', $item->id) }}">
                                                    <i class="mdi mdi-pencil-outline me-2"></i> Edit
                                                </a>
                                            </li>
                                            <li>
                                                <form action="{{ route('sessions.destroy', $item->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item" onclick="return confirm('Delete this sessions?')">
                                                        <i class="mdi mdi-delete-outline me-2"></i> Delete
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection