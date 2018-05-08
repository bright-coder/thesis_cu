<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests\RTMRequest;
use App\Http\Controllers\Controller;
use App\Library\GuardProject;
use App\Model\RequirementTraceabilityMatrix;
use App\Model\RequirementTraceabilityMatrixRelation;
use App\Model\FunctionalRequirement;
use App\Model\TestCase;

class RTMController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
    public function store(RTMRequest $request, $projectName)
    {
        $guard = new GuardProject($request->bearerToken());
        $project = $guard->getProject($projectName);

        if (!$project) {
            return response()->json(['msg' => 'Bad Request'], 400);
        }

        $request = $request->json()->all();
        $requirementTraceabilityMatrix = new RequirementTraceabilityMatrix;
        $requirementTraceabilityMatrix->projectId = $project->id;
        $prefix = $project->prefix;
        //$requirementTraceabilityMatrix->version = $request['requirementTraceabilityMatrix']['version'];
        $requirementTraceabilityMatrix->save();
        foreach ($request as $relation) {
            foreach ($relation['testCaseNos'] as $testCaseNo) {
                $requirementTraceabilityMatrixRelation = new RequirementTraceabilityMatrixRelation;

                $requirementTraceabilityMatrixRelation->requirementTraceabilityMatrixId = $requirementTraceabilityMatrix->id;
                $requirementTraceabilityMatrixRelation->functionalRequirementId = FunctionalRequirement::where([
                    'projectId' => $project->id,
                    'no' => "{$prefix}-FR-{$relation['functionalRequirementNo']}",
                ])->first()->id;
                $requirementTraceabilityMatrixRelation->testCaseId = TestCase::where([
                    'projectId' => $project->id,
                    'no' => "{$prefix}-TC-{$testCaseNo}",
                ])->first()->id;
                $requirementTraceabilityMatrixRelation->save();
            }
        }
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
                $rtm = RequirementTraceabilityMatrix::select('id')->where('projectId', '=', $project->id)->first();
                RequirementTraceabilityMatrixRelation::where('requirementTraceabilityMatrixId', '=', $rtm->id)->delete();
                RequirementTraceabilityMatrix::where('id', '=', $rtm->id)->delete();
            }
            else {
                $functionalRequirementId = FunctionalRequirement::where([
                    ['no', $functionalRequirementNo],
                    ['projectId', $project->id]
                ])->first()->id;
                RequirementTraceabilityMatrixRelation::where('functionalRequirementId', '=', $functionalRequirementId)->delete();
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
             return response()->json(['msg' => 'Internal Server Error.'],500);
        }
        return response()->json(['msg' => 'Delete Requirement Traceability Matrix.'],200);
    }
}
