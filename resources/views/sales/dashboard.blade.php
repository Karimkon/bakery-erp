@extends('sales.layouts.app')
@section('title','Sales Dashboard')
@section('content')
    <h2>Hello {{ auth()->user()->name }}</h2>
    <p class="text-muted">Track daily sales and cashier reports.</p>
@endsection
