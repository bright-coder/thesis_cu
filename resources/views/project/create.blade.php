@extends('layouts.app') 
@section('content')
<div class="container">
    <div class="card">
        <div class="card-header bg-success text-white">
            Create a new Project
        </div>
        <div class="card-body">
            <project-form access-token="{{ Auth::user()->accessToken }}" request-type="create"></project-form>
        </div>
    </div>
</div>
@endsection