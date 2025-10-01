@extends('admin.layouts.app')
@section('title','Manage Users')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2">
    <h3 class="mb-2 mb-md-0"><i class="bi bi-people-fill me-1"></i> Users</h3>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
        <i class="bi bi-plus-circle"></i> Register User
    </a>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="table-responsive shadow-sm rounded">
    <table class="table table-striped table-hover align-middle mb-0">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $u)
            <tr>
                <td>{{ $u->id }}</td>
                <td>{{ $u->name }}</td>
                <td>{{ $u->email }}</td>
                <td>
                    @php
                        $roleColors = ['admin'=>'danger','driver'=>'primary','finance'=>'success','inventory'=>'warning','agent'=>'info','sales'=>'secondary','chef'=>'dark','manager'=>'purple'];
                        $color = $roleColors[$u->role] ?? 'secondary';
                    @endphp
                    <span class="badge bg-{{ $color }}">{{ ucfirst($u->role) }}</span>
                </td>
                <td class="text-center">
                    <div class="d-flex justify-content-center gap-1 flex-wrap">
                        <a href="{{ route('admin.users.show',$u) }}" class="btn btn-info btn-sm" title="View">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('admin.users.edit',$u) }}" class="btn btn-warning btn-sm" title="Edit">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <form action="{{ route('admin.users.destroy',$u) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('Delete this user?')" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center text-muted">No users found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3 d-flex justify-content-end">
    {{ $users->links('pagination::bootstrap-5') }}
</div>
@endsection
