<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Library\State\ChangeAnalysis;
use Illuminate\Http\Request;

class ProjectApiController extends Controller
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

        $currentStateNo = 1;
        while ($currentStateNo <= ChangeAnalysis::LAST_STATE_NO) {
            if (!$changeAnalysis->process()) {
                break;
            }

            ++$currentStateNo;
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
