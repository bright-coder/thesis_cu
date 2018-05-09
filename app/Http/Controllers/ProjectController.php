<?php

namespace App\Http\Controllers;

use Validator;
use Auth;
use Illuminate\Http\Request;

class ProjectController extends Controller
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
        return view('project.main');
    }

    public function show($name){
        return view('project.show');
    }

    /**
     * Show the form to create a new project.
     *
     * @return void
     */
    public function create()
    {
        return view('project.create');
    }

    /**
     * Store a new project.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        return \redirect("project");
    }
}
