<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\User;
use App\Model\Project;
use App\Library\CustomModel\DBTargetConnection;
use App\Library\Builder\DatabaseBuilder;
use DB;
use App\Library\GuardProject;
use Illuminate\Http\Request;

class DatabaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $projectName)
    {
        $guard = new GuardProject($request->bearerToken());
        $project = $guard->getProject($projectName);

        if (!$project) {
            return response()->json(['msg' => 'Not found your project.'], 200);
        }


        $dbCon = DBTargetConnection::getInstance('sqlsrv', $project->dbServer,$project->dbPort,$project->dbName,$project->dbUsername,$project->dbPassword);
        if( !$dbCon->Connect()) {
            return response()->json(['msg' => 'Cannot Connect to Target Database.'], 400);
        }
        else {
            $databaseBuilder = new DatabaseBuilder($dbCon);
            $databaseBuilder->setUpTablesAndColumns();
        }
        //dd($databaseBuilder->getDatabase());
        return response()->json($databaseBuilder->getDatabase()->toArray(),200);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
    }
}
