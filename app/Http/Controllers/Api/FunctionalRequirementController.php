<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\FunctionalRequirementRequest;
use App\Library\GuardProject;
use DB;
use App\Model\FunctionalRequirement;
use App\Model\FunctionalRequirementInput;
use App\Model\Project;

class FunctionalRequirementController extends Controller
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

        if(!$project) {
            return response()->json(['msg' => 'Bad Request'],400);
        }

        $result = [];
        $statusCode = 202;
        $frList = FunctionalRequirement::where('projectId', $project->id)->get();
        foreach ($frList as $index => $fr) {
            $result[$index] = $fr;
            $frInput = FunctionalRequirementInput::where([
                ['frId', $fr->id],
                ['activeFlag', 'Y']
                ])->get();
            if($frInput != null) {
                $result[$index]['inputs'] = $frInput;
            } 
        }
        if(count($result) > 0 ) {
            $statusCode = 200;
        }
        return response()->json($result, $statusCode);
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
    public function store(FunctionalRequirementRequest $request, $projectName)
    {
        $guard = new GuardProject($request->bearerToken());
        $project = $guard->getProject($projectName);

        if(!$project) {
            return response()->json(['msg' => 'Bad Request'],400);
        }

        $request = $request->json()->all();
        DB::beginTransaction();
        try {
            $prefix = Project::find($project->id)->prefix;
            foreach ($request as $frImport) {
                $fr = new FunctionalRequirement;
                $fr->projectId = $project->id;
                $fr->no = "{$prefix}-FR-{$frImport['no']}";
                $fr->description = \array_key_exists('desc', $frImport) ? $frImport['desc'] : null;
                $fr->save();
                foreach ($frImport['inputs'] as $input) {
                    $frInput = new FunctionalRequirementInput;
                    $frInput->frId = $fr->id;
                    $frInput->name = $input['name'];
                    $frInput->dataType = $input['dataType'];
                    $frInput->length = \array_key_exists('length', $input) ? $input['length'] : null;
                    $frInput->precision = \array_key_exists('precision', $input) ? $input['precision'] : null;
                    $frInput->scale = \array_key_exists('scale', $input) ? $input['scale'] : null;
                    $frInput->nullable = $input['nullable'];
                    $frInput->unique = $input['unique'];
                    $frInput->min = \array_key_exists('min', $input) ? $input['min'] : null;
                    $frInput->max = \array_key_exists('max', $input) ? $input['max'] : null;
                    $frInput->tableName = $input['tableName'];
                    $frInput->columnName = $input['columnName'];
                    $frInput->activeFlag = 'Y';
                    $frInput->save();
                }
            }
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            return response()->json(['msg' => "Internal Sever Error."], 500);
        }
        return response()->json(['msg' => "Insert success"], 200);

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
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request ,$projectName, $functionalRequirementNo = "all")
    {
        $guard = new GuardProject($request->bearerToken());
        $project = $guard->getProject($projectName);

        if (!$project) {
            return response()->json(['msg' => 'Bad Request'], 400);
        }

        DB::beginTransaction();
        try {
            if ($functionalRequirementNo === "all") {
                $frs = FunctionalRequirement::select('id')->where('projectId', $project->id)->get();
                foreach ($frs as $fr) {
                    FunctionalRequirementInput::where(['frId', $fr->id])->delete();
                    FunctionalRequirement::where('id', $fr->id)->delete();
                }
            }
            else {
                $functionalRequirementId = FunctionalRequirement::where([
                    ['no', $functionalRequirementNo],
                    ['projectId', $project->id]
                ])->first()->id;
                FunctionalRequirementInput::where('frId', $functionalRequirementId)->delete();
                FunctionalRequirement::where('id', $functionalRequirementId)->delete();
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
             return response()->json(['msg' => 'Internal Server Error.'],500);
        }
        return response()->json(['msg' => 'Delete Functional Requirement success.'],200);
    }
}
