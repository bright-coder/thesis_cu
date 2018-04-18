@extends('layouts.app') 
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
                    <span id="selectProjectMenu" style="display:none">&nbsp;&nbsp;>&nbsp;&nbsp;
                                <select id="selectFr" data-live-search="true" class="selectpicker" data-style="btn-primary" data-width="fit" title="Choose your functional Requirement.">
                                </select>
                            </span>


                </div>
                <div class="card-body" id="content" style="display: none">
                    <div id="inputChangeMenu" style="display: none">
                        <h3>Description</h3>
                        <hr>
                        <span id="descText"></span>
                        <br>
                        <br>
                        <br>
                        <h3>Functional Requirement Input List</h3>
                        <hr>
                        <div class="container-fluid" id="table">
                            <div class="table-responsive" id="inputFrTable">
                                <table class="table table-striped" id="inputFrTable">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Data Type</th>
                                            <th>Length</th>
                                            <th>Precision</th>
                                            <th>Scale</th>
                                            <th>Default</th>
                                            <th>Nullable</th>
                                            <th>Unique</th>
                                            <th>Min</th>
                                            <th>Max</th>
                                            <th>Column Name</th>
                                            <th>Table Name</th>
                                            <th><button name="addInput" class="btn btn-success">Add new input</button></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white has-shadow">
            <div class="card" id="changeList" style="display:none">
                <div class="card-header">
                    <select id="selectProject" class="selectpicker" data-style="btn-primary" title="Choose your project.">
                    </select>
                    <span id="selectProjectMenu" style="display:none">&nbsp;&nbsp;>&nbsp;&nbsp;
                                <select id="selectFr" data-live-search="true" class="selectpicker" data-style="btn-primary" data-width="fit" title="Choose your functional Requirement.">
                                </select>
                            </span>


                </div>
                <div class="card-body" id="content" style="display: none">
                    <div id="inputChangeMenu" style="display: none">
                        <h3>Description</h3>
                        <hr>
                        <span id="descText"></span>
                        <br>
                        <br>
                        <br>
                        <h3>Functional Requirement Input List</h3>
                        <hr>
                        <div class="container-fluid" id="table">
                            <div class="table-responsive" id="inputFrTable">
                                <table class="table table-striped" id="inputFrTable">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Data Type</th>
                                            <th>Length</th>
                                            <th>Precision</th>
                                            <th>Scale</th>
                                            <th>Default</th>
                                            <th>Nullable</th>
                                            <th>Unique</th>
                                            <th>Min</th>
                                            <th>Max</th>
                                            <th>Column Name</th>
                                            <th>Table Name</th>
                                            <th><button name="addInput" class="btn btn-success">Add new input</button></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</section>

<div id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 id="modalHeader" class="modal-title"></h1>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">

            </div>
            <div class="modal-footer">
                {{-- <button type="button" data-dismiss="modal" class="btn btn-secondary">Close</button> --}}
            </div>
        </div>
    </div>
</div>
@endsection
 
@section('customCSS')
<link rel="stylesheet" href="{{ asset('vendor/bootstrap-select/css/bootstrap-select.min.css')}}">
@endsection
 
@section('customJS')
<script src="{{ asset('vendor/bootstrap-select/js/bootstrap-select.min.js')}}"></script>
<script src="{{ asset('js/changeRequest.func.js') }}"></script>
<script src="{{ asset('js/changeRequest.js') }}"></script>
@endsection