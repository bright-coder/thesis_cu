@extends('layouts.authen') 
@section('content') {{--
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-default">
                <div class="card-header">Login</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="form-group row">
                            <label for="email" class="col-sm-4 col-form-label text-md-right">E-Mail Address</label>
                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}"
                                    required autofocus> @if ($errors->has('email'))
                                <span class="invalid-feedback">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span> @endif
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">Password</label>
                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password"
                                    required> @if ($errors->has('password'))
                                <span class="invalid-feedback">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span> @endif
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-6 offset-md-4">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Remember Me
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    Login
                                </button>
                                <a class="btn btn-link" href="{{ route('password.request') }}">
                                    Forgot Your Password?
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div> --}}
<div class="page login-page">
    <div class="container d-flex align-items-center">
        <div class="form-holder has-shadow">
            <div class="row">
                <!-- Logo & Information Panel-->
                <div class="col-lg-6">
                    <div class="info d-flex align-items-center">
                        <div class="content">
                            <div class="logo">
                                <h1>Impact Analysis</h1>
                            </div>
                            <p></p>
                        </div>
                    </div>
                </div>
                <!-- Form Panel    -->
                <div class="col-lg-6 bg-white">
                    <div class="form d-flex align-items-center">
                        <div class="content">
                            <form id="login-form" method="post" action="{{ route('login') }}">
                                @csrf
                                <div class="form-group">
                                    <input id="email" type="text" name="email" required autofocus class="input-material{{ $errors->has('email') ? ' is-invalid' : '' }}"
                                        value="{{ old('email') }}">
                                    <label for="email" class="label-material">E-Mail Address</label> @if ($errors->has('email'))
                                    <label id="email-error" class="error" for="email">{{ $errors->first('email') }}.</label>                                    @endif
                                </div>
                                <div class="form-group">
                                    <input id="password" type="password" name="password" required="" class="input-material {{ $errors->has('password') ? ' is-invalid' : '' }}"
                                        required>
                                    <label for="password" class="label-material">Password</label> @if ($errors->has('password'))
                                    <label id="password-error" class="error" for="password">{{ $errors->first('password') }}.</label>                                    @endif
                                </div>
                                <div class="form-group terms-conditions">
                                    <input id="remember" type="checkbox" class="checkbox-template" name="remember" {{ old( 'remember') ? 'checked' : '' }}>
                                    <label for="remember">Remember Me</label>
                                </div>
                                <button id="login" type="submit" class="btn btn-primary">Login</button>
                                <!-- This should be submit button but I replaced it with <a> for demo purposes-->
                            </form><a href="{{ route('password.request')  }}" class="forgot-pass">Forgot Password?</a><br><small>Do not have an account? </small>
                            <a href="{{ route('register') }}" class="signup">Signup</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="copyrights text-center">
        <p>Design by <a href="https://bootstrapious.com/admin-templates" class="external">Bootstrapious</a>
        </p>
    </div>
</div>
@endsection