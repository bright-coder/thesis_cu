@extends('layouts.app') 
@section('content')
<header class="page-header">
    <div class="container-fluid">
        <h2 class="no-margin-bottom" id="header"></h2>
    </div>
</header>
<div class="breadcrumb-holder container-fluid">
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('project') }}">project</a></li>
        <li class="breadcrumb-item active" id="headerBread"></li>
    </ul>
</div>
<section class="forms">
    <div class="container-fluid">
        <div class="bg-white has-shadow">
            @include('layouts.preload')
            <div class="card" style="display: none;" id="menu">
                <div class="card-header pt-2 pb-2">
                    <ul class="nav nav-pills card-header-pills" id="pills-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="pills-project-tab" data-toggle="pill" href="#pills-project" role="tab" aria-controls="pills-home"
                                aria-selected="true">Project</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pills-db-tab" data-toggle="pill" href="#pills-db" role="tab" aria-controls="pills-profile" aria-selected="false">Database</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pills-fr-tab" data-toggle="pill" href="#pills-fr" role="tab" aria-controls="pills-fr" aria-selected="false">Functional Requirements</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pills-tc-tab" data-toggle="pill" href="#pills-tc" role="tab" aria-controls="pills-tc" aria-selected="false">Test Cases</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pills-rtm-tab" data-toggle="pill" href="#pills-rtm" role="tab" aria-controls="pills-rtm" aria-selected="false">Requirement Traceability Martix</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="pills-tabContent">
    @include('project.show.project')
    @include('project.show.database')
    @include('project.show.fr')
    @include('project.show.tc')
    @include('project.show.rtm')
                    </div>
                </div>
            </div>
        </div>
</section>
@endsection
 
@section('customCSS')
<link rel="stylesheet" href="{{ asset('vendor/ladda/ladda-themeless.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}">
@endsection
 
@section('customJS')
<script src="{{ asset('js/bootstrap-filestyle.min.js') }}"></script>
<script src="{{ asset('js/jquery.serializejson.min.js') }}"></script>
<script src="{{ asset('vendor/ladda/spin.min.js') }}"></script>
<script src="{{ asset('vendor/ladda/ladda.min.js') }}"></script>
<script src="{{ asset('js/xlsx.full.min.js') }}"></script>
<script src="{{ asset('vendor/datatables.net/js/jquery.dataTables.js') }}"></script>
<script src="{{ asset('vendor/datatables.net-bs4/js/dataTables.bootstrap4.js') }}"></script>
<script src="{{ asset('vendor/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('vendor/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js') }}"></script>
<script src="{{ asset('js/project/show.func.js') }}"></script>
<script src="{{ asset('js/project/show.js') }}"></script>
@endsection