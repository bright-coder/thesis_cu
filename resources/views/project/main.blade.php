@extends('layouts.app') 
@section('content')
<header class="page-header">
    <div class="container-fluid">
    <h2 class="no-margin-bottom">Project</h2>
    </div>
</header>
<section class="dashboard-counts no-padding-bottom">
    <div class="container-fluid">
        <div class="bg-white has-shadow">
            <div class="card">
                <div class="card-header pt-2 pb-2">
                    <a href="{{ route('projectCreate') }}" class="btn btn-primary"><i class="fa fa-plus"></i> New Project</a>
                </div>
                <div class="card-body" id="content">                   
                </div>
            </div>
        </div>
</section>

@endsection

@section('customJS')
 <script src="{{ asset('js/mainProject.js') }}"></script>
@endsection