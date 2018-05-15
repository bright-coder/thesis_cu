@extends('layouts.app')
@section('content')
<change-request-form selected-project-init="-" access-token="{{ Auth::user()->accessToken }}"></change-request-form>
@endsection