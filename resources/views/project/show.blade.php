@extends('layouts.app') 
@section('content')
    <project-form
        access-token="{{ Auth::user()->accessToken }}"
        request-type="update"
        project-name-init="{{ $projectNameInit }}">
    </project-form>
@endsection

@section('customJS')
@endsection