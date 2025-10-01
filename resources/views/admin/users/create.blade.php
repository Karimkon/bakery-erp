@extends('admin.layouts.app')
@section('title','Register User')

@section('content')
<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
        <h4 class="mb-0"><i class="bi bi-person-plus-fill me-1"></i> Add New User</h4>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf

            <div class="mb-3">
                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" 
                       class="form-control @error('name') is-invalid @enderror" required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" 
                       class="form-control @error('email') is-invalid @enderror" required>
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
                    <option value="">-- Select Role --</option>
                    @php
                        $roles = ['admin'=>'Admin','chef'=>'Chef','sales'=>'Sales','finance'=>'Finance','driver'=>'Driver','manager'=>'Manager'];
                    @endphp
                    @foreach($roles as $key => $label)
                        <option value="{{ $key }}" @selected(old('role') == $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                <input type="password" id="password" name="password" 
                       class="form-control @error('password') is-invalid @enderror" required>
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i> Save</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary ms-2"><i class="bi bi-arrow-left me-1"></i> Back</a>
        </form>
    </div>
</div>
@endsection
