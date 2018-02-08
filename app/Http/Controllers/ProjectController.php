<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Project;
use App\ChangeRequest;
use App\FunctionalRequirement;

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
        
        $data = $request->json()->all(); // jsonObject to Array;
        /**
         * example { "msg" : "test message" } => ['msg']
         */

        //
        // DB::beginTransaction();
        // try {
        //     $project = new Project;
        //     $project->projectId = $request->projectInfo;
        //     $project->dbHostName = $request->connectDatabaseInfo["hostname"];
        //     $project->dbName = $request->connectDatabaseInfo["databaseName"];
        //     $project->dbUsername = $request->connectDatabaseInfo["username"];
        //     $project->dbPassword = $request->connectDatabaseInfo["password"];
        //     $project->dbPort = $request->connectDatabaseInfo["port"];
        //     $project->save();
            
        //     $functionalRequirement = new FunctionalRequirement;
        //     foreach ($request-> as $key => $value) {
        //         # code...
        //     }
        //     $functionalRequirement->FRNo = $request->

        //     DB::commit();
        //     $msg = "CREATED";
        //     $statusCode = 201;

        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     $msg = $e->getMessage();
        //     $statusCode = 303;
        // }
        
       // dd(json_decode($request->getContent(), true));
        return response()->json(['msg' => $data['functionalRequirements']], 200);
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
