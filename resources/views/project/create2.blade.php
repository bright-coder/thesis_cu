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
                        <form action="{{ route('project') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="pName">Project Name <i class="fa fa-asterisk" style="color:red"></i></label>
                                        <input type="text" class="form-control{{ $errors->has('pName') ? ' is-invalid' : '' }}" name="pName" value="{{ old('pName') }}" aria-describedby="pNameHelp" placeholder="Enter Your Project Name">
                                        @if ($errors->has('pName'))
                                            <span class="invalid-feedback">
                                                <strong>{{ $errors->first('pName') }}</strong>
                                            </span> 
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="line"></div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="dbName">Database Name <i class="fa fa-asterisk" style="color:red"></i></label>
                                        <input type="text" class="form-control{{ $errors->has('dbName') ? ' is-invalid' : '' }}" name="dbName" value="{{ old('dbName') }}" aria-describedby="dbNameHelp" placeholder="Enter Your Database Name">
                                        @if ($errors->has('dbName'))
                                            <span class="invalid-feedback">
                                                <strong>{{ $errors->first('dbName') }}</strong>
                                            </span> 
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label for="dbHost">Database Host <i class="fa fa-asterisk" style="color:red"></i></label>
                                        <input type="text" class="form-control {{ $errors->has('dbHost') ? ' is-invalid' : '' }}" name="dbHost" value="{{ old('dbHost') }}" aria-describedby="dbHostHelp" placeholder="Enter Your Database Host">
                                        @if ($errors->has('dbHost'))
                                            <span class="invalid-feedback">
                                                <strong>{{ $errors->first('dbHost') }}</strong>
                                            </span> 
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label for="dbPort">Database Port</label>
                                        <input type="number" class="form-control" name="dbPort" value="{{ old('dbPort') }}" aria-describedby="dbPortHelp" placeholder="Enter Your Database Port">
                                    </div>
                                    <div class="form-group">
                                        <label for="dbTypeSqlSrv">Database Type <i class="fa fa-asterisk" style="color:red"></i></label>
                                        <div class="i-checks">
                                            <input id="dbTypeSqlSrv" type="radio" checked="" value="1" name="dbType" {{ old( 'dbType')==1 ? "checked" : "" }} class="radio-template">
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
                                        <label for="dbUser">Username <i class="fa fa-asterisk" style="color:red"></i></label>
                                    <input type="text" class="form-control{{ $errors->has('dbUser') ? ' is-invalid' : '' }}" value="{{ old('dbUser') }}" name="dbUser" aria-describedby="dbUsereHelp" placeholder="Enter Your Username to Connect this Database.">
                                        @if($errors->has('dbUser'))
                                            <span class="invalid-feedback">
                                                <strong>{{ $errors->first('dbUser') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label for="dbPassword">Password <i class="fa fa-asterisk" style="color:red"></i></label>
                                        <input type="password" class="form-control {{ $errors->has('dbPassword') ? ' is-invalid' : '' }}" name="dbPassword" aria-describedby="dbPasswordHelp" placeholder="Enter Your Username to Connect this password.">                                        {{-- <small id="dbPasswordHelp" class="form-text text-muted">We'll never share your email with anyone else.</small>                                        --}}
                                        @if($errors->has('dbPassword'))
                                            <span class="invalid-feedback">
                                                <strong>{{ $errors->first('dbPassword') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="line"></div>
                            <div class="form-group">
                                <button class="btn btn-primary btn-lg" type="submit"><i class="fa fa-save"></i> Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
@endsection