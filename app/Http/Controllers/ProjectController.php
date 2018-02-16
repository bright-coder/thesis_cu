<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Library\State\ImportState;
use App\Library\State\AnalyzeImpactDBState;
use App\Library\State\ChangeAnalysis;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return response()->json(['method' => 'get']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return response()->json(['method' => 'get create']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $request = $request->json()->all(); // jsonObject to Array;
        /**
         * example { "msg" : "test message" } => ['msg']
         */
        /**
         *  USE SQL => DELETE FROM 'table' instead of TRUNCATE ;
         */
        $changeAnalysis = new ChangeAnalysis($request);

        while(!$changeAnalysis->isDone()) {
            $changeAnalysis->process();
        }

        // dd(json_decode($request->getContent(), true));
        return response()->json(['msg' => $changeAnalysis->getMessage()], $changeAnalysis->getStatusCode());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        return response()->json(['method' => "get {$id}"]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        return response()->json(['method' => "get {$id} edit"]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $method = $request->method();
        return response()->json(['method' => "{$method} for {$id}"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        return response()->json(['method' => "destroy for {$id}"]);
    }
}
