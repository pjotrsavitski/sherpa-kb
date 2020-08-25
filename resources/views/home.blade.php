@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body">
                    <h1>Hello, {{ Auth::user()->name }}</h1>
                    <!--h2>Country SELFIE Expert (Estonia - Estonian)</h2-->
                    <!--h2>SELFIE master</h2-->
                    <language-expert-view language="et"></language-expert-view>
                    <master-expert-view></master-expert-view>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
