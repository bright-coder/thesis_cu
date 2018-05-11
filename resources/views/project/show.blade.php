@extends('layouts.app') 
@section('content')
<project-show access-token="{{ Auth::user()->accessToken }}" request-type="update" project-name-init="{{ $projectNameInit }}">
</project-show>
@endsection
 
@section('customJS')
@endsection