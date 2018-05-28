<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChangeRequestController extends Controller
{
            /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('changeRequest.main');    
    }

    public function list($projectName){
        return view('changeRequest.listByProject',['projectName' => $projectName]);
    }

    public function create()
    {
        return view('changeRequest.create');
    }

    public function show($projectName, $changeRequestId) {
        return view('changeRequest.show',['projectName' => $projectName, 'changeRequestId' => $changeRequestId]);
    }
    
}
