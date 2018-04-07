<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use App\Library\CustomModel\DBTargetConnection;
use App\Library\Builder\DatabaseBuilder;
use DB;
use Illuminate\Http\Request;

class DatabaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $projectId)
    {
        $user = User::select('id')->where('accessToken', '=', $request->bearerToken())->first();
        $project = DB::table('PROJECT')
            ->select('dbServer', 'dbName','dbUsername','dbPassword')
            ->where('userId', '=', $user->id)
            ->where('id','=', $projectId)
            ->get();
        if (count($project) <= 0) {
            return response()->json(['msg' => 'Not found your project.'], 200);
        }
        
        $project = $project[0];

        $dbCon = DBTargetConnection::getInstance('sqlsrv', $project->dbServer,$project->dbName,$project->dbUsername,$project->dbPassword);
        if( !$dbCon->Connect()) {
            return response()->json(['msg' => 'Cannot Connect to Target Database.'], 400);
        }
        else {
            $databaseBuilder = new DatabaseBuilder($dbCon);
            $databaseBuilder->setUpTablesAndColumns();
        }
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
