<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

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

    public function index(){
        return view('changeRequest');
        
    }
    
}
