<?php

namespace App\Http\Controllers\Api;

use Validator;
use DB;
use App\User;
use App\Project;
use App\Http\Controllers\Controller;
use App\Library\State\ChangeAnalysis;
use Illuminate\Http\Request;
use App\Http\Requests\ProjectRequest;
use App\Library\CustomModel\SqlServer;

class ProjectApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = User::select('id')->where('accessToken','=',$request->bearerToken())->first();
        $projects = DB::table('PROJECT')
        ->select('id as projectId','name as projectName','dbServer','dbName')
        ->where('userId', '=', $user->id)
        ->get();
    
        if ( empty($projects) ) {
            return response()->json(['msg' => 'Not found your project.'], 200);
        }

        return response()->json($projects, 200);
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
    public function store(ProjectRequest $request)
    {
        //after validate $request
        $user = User::select('id')->where('accessToken','=',$request->bearerToken())->first();
        $request = $request->json()->all(); // jsonObject to Array;
        /**
         * example { "msg" : "test message" } => ['msg']
         */
        $db = new SqlServer(
            $request['dbServer'],
            $request['dbName'],
            $request['dbUsername'],
            $request['dbPassword']
        );
        if(!$db->Connect()){
            
            return response()->json(['msg' => "Cannot connect to database."], 400);
        }

        DB::beginTransaction();
        try {
            $project = new Project;
            $project->userId = $user->id;
            $project->name = $request['projectName'];
            $project->dbServer = $request["dbServer"];
            $project->dbName = $request["dbName"];
            $project->dbUsername = $request["dbUsername"];
            $project->dbPassword = $request["dbPassword"];
            $project->dbPort = $request["dbPort"];
            $project->dbType = $request["dbType"];
            $project->save();
            DB::commit();
        } catch(Exception $e) {
            DB::rollBack();
            return response()->json(['msg'=>"Internal Sever Error."], 500);
        }
        return response()->json(['msg' =>"Insert project success.",'projectId' => $project->id], 200);
        /**
         *  USE SQL => DELETE FROM 'table' instead of TRUNCATE ;
         */
        // $changeAnalysis = new ChangeAnalysis($request);

        // $currentStateNo = 1;
        // while ($currentStateNo <= ChangeAnalysis::LAST_STATE_NO) {
        //     if (!$changeAnalysis->process()) {
        //         break;
        //     }

        //     ++$currentStateNo;
        // }

        // // dd(json_decode($request->getContent(), true));
        // return response()->json(['msg' => $changeAnalysis->getMessage()], $changeAnalysis->getStatusCode());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request,$id)
    {
        //
        $user = User::select('id')->where('accessToken','=',$request->bearerToken())->first();
        $project = DB::table('PROJECT')
        ->select('id as projectId','name as projectName','dbServer','dbName','dbPort','dbUsername','dbPassword')
        ->where([
            ['userId', '=', $user->id],
            ['id', '=', $id]
        ])
        ->get();

        if ( empty($project) ) {
            return response()->json(['msg' => 'Not found your project.'], 200);
        }

        return response()->json($project[0],200);
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
