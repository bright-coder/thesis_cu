@extends('layouts.app')
@section('content')
<impact-result 
    project-name="{{ $projectName }}" 
    change-request-id="{{ $changeRequestId }}" 
    access-token="{{ Auth::user()->accessToken }}">
</impact-result>
@endsection