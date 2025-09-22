@extends('admin.layouts.app')
@section('title','User Details')
@section('content')
<h3>User Details</h3>
<div class="card p-3">
    <p><strong>ID:</strong> {{ $user->id }}</p>
    <p><strong>Name:</strong> {{ $user->name }}</p>
    <p><strong>Email:</strong> {{ $user->email }}</p>
    <p><strong>Role:</strong> <span class="badge bg-dark">{{ ucfirst($user->role) }}</span></p>
</div>
<a href="{{ route('admin.users.index') }}" class="btn btn-secondary mt-3">Back</a>
@endsection
