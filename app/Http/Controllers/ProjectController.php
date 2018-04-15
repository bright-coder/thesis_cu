<?php

namespace App\Http\Controllers;

use Validator;
use Auth;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

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

    public function show($id){
        return view('project.show');
    }

    /**
     * Show the form to create a new project.
     *
     * @return void
     */
    public function create()
    {
        return view('project.create2');
    }

    /**
     * Store a new project.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        // $fr = $request->file('frFile');
        // $fc = $request->file('tcFile');
        // $rtm = $request->file('rtmFile');
        // $rules = [
        //     'pName' => 'required|between:10,255',
        //     'dbName' => 'required|between:1,255',
        //     'dbHost' => 'required|between:1,255',
        //     'dbPort' => 'sometimes|nullable|numeric',
        //     'dbType' => 'required|numeric|between:1,2',
        //     'dbUser' => 'required|between:4,100',
        //     'dbPassword' => 'required|between:4,100'
        // ];
        // $message = [
        //     'required' => 'This is required.',
        //     'between' => 'This field must contain :min - :max characters.',
        //     'numeric' => 'This field must contain number only.'
        // ];
        // $validator = Validator::make($request->all(),$rules,$message)->validate();
        //\Session::flash('flash_message','Office successfully added.'.$request->name);
        //$name = $request->name." Recieved";
        //return redirect()->route('home');
        // Validate and store the new project...
        return \redirect("project");
    }
}
