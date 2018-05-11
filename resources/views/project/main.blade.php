@extends('layouts.app')
@section('content')
    <project-main
        access-token="{{ Auth::user()->accessToken }}">
    </project-main>
@endsection