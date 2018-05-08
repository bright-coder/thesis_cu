@extends('layouts.app') 
@section('content')
<section class="forms">
    <div class="container-fluid">
        <div class="row">
            <!-- Basic Form-->
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-close">
                        <div class="dropdown">
                            <button class="dropdown-toggle" id="closeCard1" aria-expanded="false" aria-haspopup="true" type="button" data-toggle="dropdown"><i class="fa fa-ellipsis-v"></i></button>
                            <div class="dropdown-menu dropdown-menu-right has-shadow" aria-labelledby="closeCard1"><a class="dropdown-item remove" href="#"> <i class="fa fa-times"></i>Close</a><a class="dropdown-item edit"
                                    href="#"> <i class="fa fa-gear"></i>Edit</a></div>
                        </div>
                    </div>
                    <div class="card-header d-flex align-items-center">
                        <h1 class="">Create a new project</h1>
                    </div>
                    <div class="card-body">
                        <form id="create">
                            @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="projectName">Project Name <i class="fa fa-asterisk" style="color:red"></i></label>
                                        <input type="text" class="form-control" name="projectName" aria-describedby="pNameHelp" placeholder="Enter Your Project Name">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="prefix">Prefix<i class="fa fa-asterisk" style="color:red"></i></label>
                                        <input type="text" class="form-control" name="prefix" aria-describedby="prfixHelp" placeholder="Enter Your Prefix e.g. PR , HS">
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
                                        <input type="number" class="form-control" name="dbPort" aria-describedby="dbPortHelp" placeholder="Enter Your Database Port" value="1433">
                                    </div>
                                    <div class="form-group">
                                        <label for="dbTypeSqlSrv">Database Type <i class="fa fa-asterisk" style="color:red"></i></label>
                                        <div class="i-checks">
                                            <input id="dbTypeSqlSrv" type="radio" checked="" value="sqlsrv" name="dbType" class="radio-template">
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
                                        <input type="password" class="form-control" name="dbPassword" aria-describedby="dbPasswordHelp" placeholder="Enter Your Password to Connect this password.">                                        {{-- <small id="dbPasswordHelp" class="form-text text-muted">We'll never share your email with anyone else.</small>                                        --}}

                                    </div>
                                </div>
                            </div>
                            <div class="line" id="lastLine"></div>
                            <div class="form-group">
                                    <button id="createBtn" data-style="zoom-out" type="submit" class="btn btn-primary btn-lg ladda-button"><span class="ladda-label"><i class="fa fa-plus"></i> CREATE</span></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
{{--  @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif  --}}
@endsection

@section('customCSS')
    <link rel="stylesheet" href="{{ asset('vendor/ladda/ladda-themeless.min.css') }}">
@endsection

@section('customJS')
    <script src="{{ asset('js/jquery.serializejson.min.js') }}"></script>
    <script src="{{ asset('vendor/ladda/spin.min.js') }}"></script>
    <script src="{{ asset('vendor/ladda/ladda.min.js') }}"></script>
    <script src="{{ asset('js/project/createProject.js') }}"></script> 
@endsection