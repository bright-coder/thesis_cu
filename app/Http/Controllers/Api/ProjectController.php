<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest;
use App\Library\CustomModel\SqlServer;
use App\Project;
use App\User;
use DB;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = User::select('id')->where('accessToken', '=', $request->bearerToken())->first();
        $projects = DB::table('PROJECT')
            ->select('id as projectId', 'name as projectName', 'dbServer', 'dbName')
            ->where('userId', '=', $user->id)
            ->get();
        if (count($projects) <= 0) {
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
        $user = User::select('id')->where('accessToken', '=', $request->bearerToken())->first();
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
        if (!$db->Connect()) {

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
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['msg' => "Internal Sever Error."], 500);
        }
        return response()->json(['msg' => "Insert project success.", 'projectId' => $project->id], 200);
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
    public function show(Request $request, $id)
    {
        //
        $user = User::select('id')->where('accessToken', '=', $request->bearerToken())->first();
        $project = DB::table('PROJECT')
            ->select('id as projectId', 'name as projectName', 'dbServer', 'dbName', 'dbPort', 'dbType', 'dbUsername', 'dbPassword')
            ->where([
                ['userId', '=', $user->id],
                ['id', '=', $id],
            ])
            ->get();

        if (count($project) <= 0) {
            return response()->json(['msg' => 'Not found your project.'], 200);
        }

        return response()->json($project[0], 200);
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
    public function update(ProjectRequest $request, $id)
    {
        //
        $user = User::select('id')->where('accessToken', '=', $request->bearerToken())->first();
        $project = DB::table('PROJECT')
            ->where([
                ['userId', '=', $user->id],
                ['id', '=', $id],
            ])
            ->get();

        if (count($project) <= 0) {
            return response()->json(['msg' => 'Not found your project.'], 200);
        }

        $db = new SqlServer(
            $request['dbServer'],
            $request['dbName'],
            $request['dbUsername'],
            $request['dbPassword']
        );
        if (!$db->Connect()) {

            return response()->json(['msg' => "Cannot connect to database."], 400);
        }

        DB::beginTransaction();
        try {
            $project = Project::find($id);
            $project->name = $request['projectName'];
            $project->dbServer = $request["dbServer"];
            $project->dbName = $request["dbName"];
            $project->dbUsername = $request["dbUsername"];
            $project->dbPassword = $request["dbPassword"];
            $project->dbPort = $request["dbPort"];
            $project->dbType = $request["dbType"];
            $project->save();
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['msg' => "Internal Sever Error."], 500);
        }

        return response()->json(['msg' => "Update Success"], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        //
        $user = User::select('id')->where('accessToken', '=', $request->bearerToken())->first();
        $project = DB::table('PROJECT')
            ->where([
                ['userId', '=', $user->id],
                ['id', '=', $id],
            ])
            ->get();

        if (count($project) <= 0) {
            return response()->json(['msg' => 'Not found your project.'], 200);
        }

        DB::beginTransaction();
        try {
            $crs = DB::table('CHANGE_REQUEST')->select('id')->where('projectId', '=', $id)->get();
            $frs = DB::table('FUNCTIONAL_REQUIREMENT')->select('id')->where('projectId', '=', $id)->get();
            $tcs = DB::table('TEST_CASE')->select('id')->where('projectId', '=', $id)->get();
            $rtm = DB::table('REQUIREMENT_TRACEABILITY_MATRIX')->select('id')->where('projectId', '=', $id)->get();
            $tables = DB::table('DATABASE_SCHEMA_TABLE')->select('id')->where('projectId', '=', $id)->get();
            foreach ($tables as $table) {
                DB::table('DATABASE_SCHEMA_CONSTRAINT')->where('tableId', '=', $table->id)->delete();
                DB::table('DATABASE_SCHEMA_COLUMN')->where('tableId', '=', $table->id)->delete();
                DB::table('DATABASE_SCHEMA_TABLE')->where('id', '=', $table->id)->delete();
            }
            foreach ($rtm as $rtmK) {
                DB::table('REQUIREMENT_TRACEABILITY_MATRIX_RELATION')->where('requirementTraceabilityMatrixId', '=', $rtmK->id)->delete();
                DB::table('REQUIREMENT_TRACEABILITY_MATRIX')->where('id', '=', $rtmK->id)->delete();
            }
            foreach ($tcs as $tc) {
                DB::table('TEST_CASE_INPUT')->where('testCaseId', '=', $tc->id)->delete();
                DB::table('TEST_CASE')->where('id', '=', $tc->id)->delete();
            }
            foreach ($frs as $fr) {
                DB::table('FUNCTIONAL_REQUIREMENT_INPUT')->where('functionalRequriementId', '=', $fr->id)->delete();
                DB::table('FUNCTIONAL_REQUIREMENT')->where('id', '=', $fr->id)->delete();
            }
            foreach ($crs as $cr) {
                DB::table('CHANGE_REQUEST_INPUT')->where('changeRequestId', '=', $cr->id)->delete();
                DB::table('CHANGE_REQUEST')->where('id', '=', $cr->id)->delete();
            }

            DB::table('PROJECT')->where('id', '=', $id)->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['msg' => "Internal Sever Error."], 500);
        }

        return response()->json(['msg' => "delete success"], 200);
    }

}
