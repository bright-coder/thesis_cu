@extends('layouts.app') 
@section('content')
<h1>Hello Main</h1>
@endsection

@section('customJS')
<script src="{{ asset('js/createProject.js') }}"></script>
@endsection