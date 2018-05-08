<?php

namespace App\Http\Controllers;

use App\Library\Node;

class HomeController extends Controller
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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $test = [
        //     ['col1' => "test", 'col2' => "test2"],
        //     ['col1' => "test", 'col2' => "test2"]
        // ];
        // $test = array_unique($test, SORT_REGULAR);
        // dd($test);
        return view('home');
    }

}
