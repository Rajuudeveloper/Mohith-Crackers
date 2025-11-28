@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
</div>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex">
                    <div>
                        <h4 class="card-title">150</h4>
                        <p class="card-text">Total Users</p>
                    </div>
                    <div class="ms-auto">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex">
                    <div>
                        <h4 class="card-title">23</h4>
                        <p class="card-text">New Today</p>
                    </div>
                    <div class="ms-auto">
                        <i class="fas fa-chart-line fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Activity</h5>
            </div>
            <div class="card-body">
                <p class="card-text">Welcome to your admin dashboard. Add your content here.</p>
            </div>
        </div>
    </div>
</div>
@endsection