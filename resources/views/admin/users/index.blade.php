@extends('admin.layouts.app')
@section('title','Manage Users')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h3>Users</h3>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle"></i> Add User
    </a>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<table class="table table-striped">
    <thead class="table-dark">
        <tr>
            <th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $u)
        <tr>
            <td>{{ $u->id }}</td>
            <td>{{ $u->name }}</td>
            <td>{{ $u->email }}</td>
            <td><span class="badge bg-secondary">{{ ucfirst($u->role) }}</span></td>
            <td>
                <a href="{{ route('admin.users.show',$u) }}" class="btn btn-info btn-sm">View</a>
                <a href="{{ route('admin.users.edit',$u) }}" class="btn btn-warning btn-sm">Edit</a>
                <form action="{{ route('admin.users.destroy',$u) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger btn-sm" onclick="return confirm('Delete this user?')">Delete</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{ $users->links() }}
@endsection
