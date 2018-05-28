@extends('layouts.app') 
@section('content')
<div class="container">
    <div class="card">
        <div class="card-header bg-primary text-white">
            Change Request History
        </div>
        <div class="card-body">
            <h5 class="card-title">{{ $projectName }}</h5>
            <hr>
            <change-request-list access-token="{{ Auth::user()->accessToken }}" project-name="{{ $projectName }}"></change-request-list>
        </div>
    </div>
</div>
@endsection