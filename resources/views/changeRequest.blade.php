@extends('layouts.app');

@section('content')
<header class="page-header">
    <div class="container-fluid">
    <h2 class="no-margin-bottom">Change Request</h2>
    </div>
</header>
<section>
    <div class="container-fluid">
        <div class="bg-white has-shadow">
            @include('layouts.preload')
            <div class="card" id="menu" style="display:none">
                <div class="card-header">
                    <select id="selectProject" class="selectpicker" data-style="btn-primary" title="Choose your project.">
                      </select>
                </div>
                <div class="card-body" id="content" style="display: none">
                                     
                </div>
            </div>
        </div>
</section>
@endsection

@section('customCSS')
<link rel="stylesheet" href="{{ asset('vendor/bootstrap-select/css/bootstrap-select.min.css')}}">
@endsection

@section('customJS')
<script src="{{ asset('vendor/bootstrap-select/js/bootstrap-select.min.js')}}"></script>
<script src="{{ asset('js/changeRequest.js') }}"></script>
@endsection