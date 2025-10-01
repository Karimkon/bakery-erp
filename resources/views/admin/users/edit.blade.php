@extends('admin.layouts.app')
@section('title','Edit User')
@section('content')
<h3>Edit User</h3>
<form method="POST" action="{{ route('admin.users.update',$user) }}">
    @csrf @method('PUT')
    <div class="mb-3">
        <label>Name</label>
        <input type="text" name="name" value="{{ $user->name }}" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" value="{{ $user->email }}" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Back Debt (UGX)</label>
        @if($user->role == 'driver')
            <input type="number" name="back_debt" value="{{ old('back_debt', $user->back_debt) }}" class="form-control" min="0">
        @else
            <input type="number" name="back_debt" value="0" class="form-control" readonly>
        @endif
    </div>

    <div class="mb-3">
        <label>Role</label>
        <select name="role" class="form-select">
            <option value="admin" @selected($user->role=='admin')>Admin</option>
            <option value="manager" @selected($user->role=='manager')>Manager</option>
            <option value="chef" @selected($user->role=='chef')>Chef</option>
            <option value="driver" @selected($user->role=='driver')>Driver</option>
            <option value="sales" @selected($user->role=='sales')>Sales</option>
            <option value="finance" @selected($user->role=='finance')>Finance</option>
        </select>
    </div>
    <div class="mb-3">
        <label>Password (leave blank to keep current)</label>
        <input type="password" name="password" class="form-control">
    </div>
    <div class="mb-3">
        <label>Confirm Password</label>
        <input type="password" name="password_confirmation" class="form-control">
    </div>
    <button class="btn btn-primary">Update</button>
</form>
@endsection
