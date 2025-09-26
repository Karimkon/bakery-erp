@extends('finance.layouts.app')
@section('title','Finance Dashboard')

@section('content')
<div class="row g-3">

<div class="row g-4">
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6>Total Sales</h6>
                <h4>{{ number_format($totalSales,0) }} UGX</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6>Commission</h6>
                <h4>{{ number_format($totalComm,0) }} UGX</h4>
            </div>
        </div>
    </div>


    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6>Total Expenses</h6>
                <h4>{{ number_format($totalExpenses,0) }} UGX</h4>
            </div>
        </div>
    </div>
</div>


</div>
@endsection
