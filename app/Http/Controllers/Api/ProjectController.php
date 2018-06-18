<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest;
use App\Library\CustomModel\SqlServer;
use App\Model\Project;
use App\Model\User;
use DB;
use App\Library\GuardProject;
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
        $guard = new GuardProject($request->bearerToken());
        $projects = $guard->getAllProject();
        if (count($projects) <= 0) {
            return response()->json(['msg' => 'Not found your project.'], 204);
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
        $user = User::select('id')->where('accessToken', $request->bearerToken())->first();
        $request = $request->json()->all(); // jsonObject to Array;
        /**
         * example { "msg" : "test message" } => ['msg']
         */
        $db = new SqlServer(
            $request['dbServer'],
            $request['dbPort'],
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
            $project->prefix = $request['prefix'];
            $project->save();
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['msg' => "Internal Sever Error."], 500);
        }
        return response()->json(['msg' => "Insert project success.", 'projectId' => $project->id], 200);

    }

    /**
     * Display the specified resource.
     *
     * @param  string  $name
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $name)
    {
        
        $guard = new GuardProject($request->bearerToken());
        $project = $guard->getProject($name);
        
        if(!$project) {
            return response()->json(['msg' => 'Bad Request'], 400); 
        }

        return response()->json($project, 200);
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
     * @param  string  $name
     * @return \Illuminate\Http\Response
     */
    public function update(ProjectRequest $request, $name)
    {
        $guard = new GuardProject($request->bearerToken());
        $project = $guard->getProject($name);

        if(!$project) {
            return response()->json(['msg' => 'Bad Request'], 400); 
        }

        $db = new SqlServer(
            $request['dbServer'],
            $request['dbPort'],
            $request['dbName'],
            $request['dbUsername'],
            $request['dbPassword']
        );
        if (!$db->Connect()) {

            return response()->json(['msg' => "Cannot connect to database."], 400);
        }

        DB::beginTransaction();
        try {

            //$project = Project::where($project-)
            //$project->name = $request['projectName'];
            $project->dbServer = $request["dbServer"];
            $project->dbName = $request["dbName"];
            $project->dbUsername = $request["dbUsername"];
            $project->dbPassword = $request["dbPassword"];
            $project->dbPort = $request["dbPort"];
            $project->dbType = $request["dbType"];
            $project->prefix = $request["prefix"];
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
     * @param  string  $name
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $name)
    {
        //
        $guard = new GuardProject($request->bearerToken());
        $project = $guard->getProject($name);

        if (!$project) {
            return response()->json(['msg' => 'Not found your project.'], 401);
        }

        DB::beginTransaction();
        try {
            $crs = DB::table('CHANGE_REQUEST')->select('id')->where('projectId', $project->id)->get();
            $frs = DB::table('FUNCTIONAL_REQUIREMENT')->select('id')->where('projectId',  $project->id)->get();
            $tcs = DB::table('TEST_CASE')->select('id')->where('projectId', $project->id)->get();
            $rtm = DB::table('REQUIREMENT_TRACEABILITY_MATRIX')->where('projectId', $project->id)->delete();
            foreach ($tcs as $tc) {
                DB::table('TEST_CASE_INPUT')->where('tcId', $tc->id)->delete();
                DB::table('TEST_CASE')->where('id', $tc->id)->delete();
            }

            foreach ($crs as $cr) {
                foreach (DB::table('CHANGE_REQUEST_INPUT')->where('crId', $cr->id)->get() as $crInput) {
                    
                    DB::table('CHANGE_REQUEST_INPUT')->where('id', $crInput->id)->delete();
                }

                foreach(DB::table('RECORD_IMPACT')->where('crId', $cr->id)->get() as $recImpact) {
                    DB::table('OLD_RECORD')->where('recImpactId', $recImpact->id)->delete();
                    DB::table('INSTANCE_IMPACT')->where('recImpactId', $recImpact->id)->delete();
                }
                DB::table('RECORD_IMPACT')->where('crId', $cr->id)->delete();

                DB::table('FR_INPUT_IMPACT')->where('crId', $cr->id)->delete();
    
                foreach(DB::table('TC_IMPACT')->where('crId', $cr->id)->get() as $tcImpact) {
                    DB::table('TC_INPUT_IMPACT')->where('tcImpactId', $tcImpact->id)->delete();
                    DB::table('TC_IMPACT')->where('id', $tcImpact->id)->delete();
                }
    
                DB::table('RTM_IMPACT')->where('crId', $cr->id)->delete();

                foreach(DB::table('COMPOSITE_CANDIDATE_KEY_IMPACT')->where('crId', $cr->id)->get() as $cckImpact) {
                    DB::table('COMPOSITE_CANDIDATE_KEY_COLUMN')->where('cckImpactId', $cckImpact->id)->delete();
                    DB::table('COMPOSITE_CANDIDATE_KEY_IMPACT')->where('id', $cckImpact->id)->delete();
                }

                foreach(DB::table('FOREIGN_KEY_IMPACT')->where('crId', $cr->id)->get() as $fkImpact) {
                    DB::table('FOREIGN_KEY_COLUMN')->where('cckImpactId', $fkImpact->id)->delete();
                    DB::table('FOREIGN_KEY_IMPACT')->where('id', $fkImpact->id)->delete();
                }
                DB::table('CHANGE_REQUEST')->where('id', $cr->id)->delete();
            }

            foreach ($frs as $fr) {
                DB::table('FUNCTIONAL_REQUIREMENT_INPUT')->where('frId', $fr->id)->delete();
                DB::table('FUNCTIONAL_REQUIREMENT')->where('id', $fr->id)->delete();
            }
            DB::table('PROJECT')->where('id', $project->id)->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['msg' => "Internal Sever Error."], 500);
        }

        return response()->json(['msg' => "delete success"], 200);
    }

}
