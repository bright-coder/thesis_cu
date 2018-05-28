@extends('layouts.app') 
@section('content')
<div class="container">
 
    <div class="card">
      <div class="card-header bg-primary text-white">
        Recent Change Request
      </div>
      <div class="card-body">
      <change-request-list access-token="{{ Auth::user()->accessToken }}" project-name="all"></change-request-list>
      </div>
    </div>
</div>
@endsection