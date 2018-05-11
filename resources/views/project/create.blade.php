@extends('layouts.app') 
@section('content')
    <project-form 
        access-token="{{ Auth::user()->accessToken }}" 
        request-type="create">
    </project-form>
@endsection

@section('customJS')
@endsection