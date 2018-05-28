@extends('layouts.app')
@section('content')
    <change-request-main access-token="{{ Auth::user()->accessToken }}"></change-request-main>
@endsection