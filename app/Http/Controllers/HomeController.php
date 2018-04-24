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
        // $users = $this->api->get('projects');
        // dd($users);
        // $node = new Node();
        // $node2 = new Node();
        // $node->addLink($node2);

        // $node3 = new Node();
        // $node4 = new Node();
        // $node2->addLink($node3);
        // $node3->addLink($node4);
        // dd($node);
        return view('main');
    }
}
