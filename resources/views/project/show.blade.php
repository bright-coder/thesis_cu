@extends('layouts.app') 
@section('content')
<header class="page-header">
    <div class="container-fluid">
        <h2 class="no-margin-bottom" id="header"></h2>
    </div>
</header>
<div class="breadcrumb-holder container-fluid">
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('project') }}">Project</a></li>
        <li class="breadcrumb-item active" id="headerBread"></li>
    </ul>
</div>
<section class="forms">
    <div class="container-fluid">
        <div class="bg-white has-shadow">
            <div class="card">
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
                        <div class="tab-pane fade show active" id="pills-project" role="tabpanel" aria-labelledby="pills-project-tab">
                            <form id="saveProject">
                                @csrf
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="projectName">Project Name <i class="fa fa-asterisk" style="color:red"></i></label>
                                            <input type="text" class="form-control" name="projectName" aria-describedby="pNameHelp" placeholder="Enter Your Project Name">
                                        </div>
                                    </div>
                                </div>
                                <div class="line"></div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="dbName">Database Name <i class="fa fa-asterisk" style="color:red"></i></label>
                                            <input type="text" class="form-control" name="dbName" aria-describedby="dbNameHelp" placeholder="Enter Your Database Name">
                                        </div>
                                        <div class="form-group">
                                            <label for="dbServer">Database Server <i class="fa fa-asterisk" style="color:red"></i></label>
                                            <input type="text" class="form-control" name="dbServer" aria-describedby="dbHostHelp" placeholder="Enter Your Database Server">
                                        </div>
                                        <div class="form-group">
                                            <label for="dbPort">Database Port <i class="fa fa-asterisk" style="color:red"></i></label>
                                            <input type="number" class="form-control" name="dbPort" aria-describedby="dbPortHelp" placeholder="Enter Your Database Port"
                                                value="1433">
                                        </div>
                                        <div class="form-group">
                                            <label for="dbTypeSqlSrv">Database Type <i class="fa fa-asterisk" style="color:red"></i></label>
                                            <div class="i-checks">
                                                <input id="dbTypeSqlSrv" type="radio" value="sqlsrv" name="dbType" class="radio-template">
                                                <label for="dbTypeSqlSrv"><small>SQL SERVER</small></label>
                                            </div>
                                            <div class="i-checks">
                                                <input id="dbTypeMySql" type="radio" name="dbType" disabled="" value="mysql" class="radio-template">
                                                <label for="dbTypeMySql"><small>MySQL</small></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="dbUsername">Username <i class="fa fa-asterisk" style="color:red"></i></label>
                                            <input type="text" class="form-control" name="dbUsername" aria-describedby="dbUsereHelp" placeholder="Enter Your Username to Connect this Database.">
                                        </div>
                                        <div class="form-group">
                                            <label for="dbPassword">Password <i class="fa fa-asterisk" style="color:red"></i></label>
                                            <input type="password" class="form-control" name="dbPassword" aria-describedby="dbPasswordHelp" placeholder="Enter Your Password to Connect this password.">                                            {{-- <small id="dbPasswordHelp" class="form-text text-muted">We'll never share your email with anyone else.</small>                                            --}}
                                        </div>
                                    </div>
                                </div>
                                <div id="showMessage"></div>
                                <div class="line"></div>
                                <div class="form-group">
                                    <button id="saveProjectBtn" data-style="zoom-out" type="submit" class="btn btn-primary btn-lg ladda-button"><span class="ladda-label"><i class="fa fa-save"></i> SAVE</span></button>
                                </div>
                                {{--
                                <div class="form-group">
                                    <button id="saveBtn" data-style="zoom-out" type="submit" class="btn btn-primary btn-lg ladda-button"><span class="ladda-label"><i class="fa fa-save"></i> Save</span></button>
                                </div> --}}
                            </form>
                        </div>
                        <div class="tab-pane fade" id="pills-db" role="tabpanel" aria-labelledby="pills-db-tab">
                        </div>
                        <div class="tab-pane fade" id="pills-fr" role="tabpanel" aria-labelledby="pills-fr-tab">
                            <form>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="frFile">Upload Your Functional Requirements (.xls, .xlsx)</label>
                                            <input type="file" id="frFile" accept=".csv,.xlsx">
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane fade" id="pills-tc" role="tabpanel" aria-labelledby="pills-tc-tab">
                            <form>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="tcFile">Upload Your Test Cases (.xls, .xlsx)</label>
                                            <input type="file" id="tcFile" accept=".csv,.xlsx">
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane fade" id="pills-rtm" role="tabpanel" aria-labelledby="pills-tc-tab">
                            <form>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="rtmFile">Upload Your Requirement Traceability Martix (.xls, .xlsx)</label>
                                            <input type="file" id="rtmFile" accept=".csv,.xlsx">
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <div class="strike">
                                <span>OR</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</section>
@endsection
 
@section('customCSS')
<link rel="stylesheet" href="{{ asset('vendor/ladda/ladda-themeless.min.css') }}">
@endsection
 
@section('customJS')
<script src="{{ asset('js/bootstrap-filestyle.min.js') }}"></script>
<script src="{{ asset('js/jquery.serializejson.min.js') }}"></script>
<script src="{{ asset('vendor/ladda/spin.min.js') }}"></script>
<script src="{{ asset('vendor/ladda/ladda.min.js') }}"></script>
<script src="{{ asset('js/xlsx.full.min.js') }}"></script>
<script src="{{ asset('js/showProject.js') }}"></script>
<script src="{{ asset('js/showDatabase.js') }}"></script>
@endsection