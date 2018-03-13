@extends('layouts.app') 
@section('content')
<header class="page-header">
    <div class="container-fluid">
    <h2 class="no-margin-bottom">Create New Project</h2>
    </div>
</header>
<section class="dashboard-counts no-padding-bottom">
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
                            <a class="nav-link" id="pills-db-tab" data-toggle="pill" href="#pills-db" role="tab" aria-controls="pills-profile" aria-selected="false">DatabaseInfo</a>
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
                            <form>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="pName">Project Name</label>
                                            <input type="text" class="form-control" id="pName" aria-describedby="pNameHelp" placeholder="Enter Your Project Name" required>
                                            <small id="pNameHelp" class="form-text text-muted">We'll never share your email with anyone else.</small>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane fade" id="pills-db" role="tabpanel" aria-labelledby="pills-db-tab">
                            <form>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="dbName">Database Name</label>
                                            <input type="text" class="form-control" id="dbName" aria-describedby="dbNameHelp" placeholder="Enter Your Database Name" required>
                                            <small id="dbNameHelp" class="form-text text-muted">We'll never share your email with anyone else.</small>
                                        </div>
                                        <div class="form-group">
                                            <label for="dbHost">Database Host</label>
                                            <input type="text" class="form-control" id="dbHost" aria-describedby="dbHostHelp" placeholder="Enter Your Database Host" required>
                                            <small id="dbHostHelp" class="form-text text-muted">We'll never share your email with anyone else.</small>
                                        </div>
                                        <div class="form-group">
                                            <label for="dbPort">Database Port</label>
                                            <input type="number" class="form-control" id="dbPort" aria-describedby="dbPortHelp" placeholder="Enter Your Database Port">
                                            <small id="dbPortHelp" class="form-text text-muted">We'll never share your email with anyone else.</small>
                                        </div>
                                        <div class="form-group">
                                            <label for="dbTypeSqlSrv">Database Type</label>
                                            <div class="i-checks">
                                                <input id="dbTypeSqlSrv" type="radio" checked="" value="1" name="dbType" checked="" class="radio-template">
                                                <label for="dbTypeSqlSrv"><small>SQL SERVER</small></label>
                                            </div>
                                            <div class="i-checks">
                                                <input id="dbTypeMySql" type="radio" name="dbType" disabled="" value="2" class="radio-template">
                                                <label for="dbTypeMySql"><small>MySQL</small></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="dbUser">Username</label>
                                            <input type="text" class="form-control" id="dbUser" aria-describedby="dbUsereHelp" placeholder="Enter Your Username to Connect this Database.">
                                            <small id="dbUserHelp" class="form-text text-muted">We'll never share your email with anyone else.</small>
                                        </div>
                                        <div class="form-group">
                                            <label for="dbPassword">Password</label>
                                            <input type="password" class="form-control" id="dbPassword" aria-describedby="dbPasswordHelp" placeholder="Enter Your Username to Connect this password.">
                                            <small id="dbPasswordHelp" class="form-text text-muted">We'll never share your email with anyone else.</small>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane fade" id="pills-fr" role="tabpanel" aria-labelledby="pills-fr-tab">
                            <form>
                                <div class="row">
                                    <div class="form-group">
                                        <label for="frFile">Upload Your Functional Requirements (.csv, .xlsx)</label>
                                        <input type="file" class="form-control-file" id="frFile" accept=".csv,.xlsx">
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane fade" id="pills-tc" role="tabpanel" aria-labelledby="pills-tc-tab">
                            <form>
                                <div class="row">
                                    <div class="form-group">
                                        <label for="tcFile">Upload Your Test Cases (.csv, .xlsx)</label>
                                        <input type="file" class="form-control-file" id="tcFile" accept=".csv,.xlsx">
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane fade" id="pills-rtm" role="tabpanel" aria-labelledby="pills-tc-tab">
                            <form>
                                <div class="row">
                                    <div class="form-group">
                                        <label for="rtmFile">Upload Your Requirement Traceability Martix (.csv, .xlsx)</label>
                                        <input type="file" class="form-control-file" id="rtmFile" accept=".csv,.xlsx">
                                    </div>
                                </div>
                            </form>
                            <div class="strike">
                                <span>OR</span>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <form id="save">
                        <div class="form-group row">
                            <div class="col-sm-4 offset-sm-4">
                                <button type="submit" class="btn btn-secondary">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
</section>
@endsection

@section('customJS')
<script src="{{ asset('js/createProject.js') }}"></script>
@endsection