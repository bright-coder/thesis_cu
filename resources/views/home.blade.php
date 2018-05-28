@extends('layouts.app') 
@section('content')
<div class="container">
 
    <div class="card">
      <div class="card-header bg-primary text-white">
        Recent Change Request
      </div>
      <div class="card-body">
      <recent-change-request access-token="{{ Auth::user()->accessToken }}"></recent-change-request>
      </div>
    </div>
</div>
@endsection