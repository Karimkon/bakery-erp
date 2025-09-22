@extends('admin.layouts.app')
@section('title','Add User')
@section('content')
<h3>Add New User</h3>
<form method="POST" action="{{ route('admin.users.store') }}">
    @csrf
    <div class="mb-3">
        <label>Name</label>
        <input type="text" name="name" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
    <label>Role</label>
    <select name="role" class="form-select" required>
        <option value="">-- Select Role --</option>
        <option value="admin" {{ old('role')=='admin' ? 'selected' : '' }}>Admin</option>
        <option value="chef" {{ old('role')=='chef' ? 'selected' : '' }}>Chef</option>
        <option value="sales" {{ old('role')=='sales' ? 'selected' : '' }}>Sales</option>
        <option value="finance" {{ old('role')=='finance' ? 'selected' : '' }}>Finance</option>
        <option value="driver" {{ old('role')=='driver' ? 'selected' : '' }}>Driver</option>
    </select>
    @error('role')<small class="text-danger">{{ $message }}</small>@enderror
</div>

    <div class="mb-3">
        <label>Password</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Confirm Password</label>
        <input type="password" name="password_confirmation" class="form-control" required>
    </div>
    <button class="btn btn-success">Save</button>
</form>
@endsection
