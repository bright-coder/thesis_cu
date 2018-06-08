<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests\RTMRequest;
use App\Http\Controllers\Controller;
use App\Library\GuardProject;
use App\Model\RequirementTraceabilityMatrix;
use App\Model\FunctionalRequirement;
use App\Model\TestCase;

class RTMController extends Controller
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
            return response()->json(['msg' => 'Bad Request'], 400);
        }

        $result = [];
        $statusCode = 202;
        $rtm = RequirementTraceabilityMatrix::where('projectId', $project->id)
            ->orderBy('frId', 'asc')->get();
        if(count($rtm) == 0) {
            return response()->json($result, $statusCode);
        }

        foreach($rtm as $relation) {
            $frId = $relation->frId;
            $frNo = FunctionalRequirement::select('no')->where('id', $frId)->first()->no;
            $testCaseNo = TestCase::where('id', $relation->testCaseId)->first()->no;
            if(\array_key_exists($frNo,$result)) {
                $result[$frNo]['testCaseNos'][] = $testCaseNo;
            }
            else {
                $result[$frNo] = [
                    'functionalRequirementNo' => $frNo,
                    'testCaseNos' => [$testCaseNo]
                ];
            }
        }

        if(count($result) > 0 ) {
            $newKeyArray = []; // for change key from HS-FR-01 to 0 ,1 , 2
            foreach($result as $obj) {
                $newKeyArray[] = $obj;
            }
            $statusCode = 200;
        }
        return response()->json($newKeyArray, $statusCode);
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

        foreach ($request as $relation) {
            foreach ($relation['testCaseNos'] as $testCaseNo) {
                $rtm = new RequirementTraceabilityMatrix;
                $rtm->projectId = $project->id;
                $rtm->frId = FunctionalRequirement::where([
                    'projectId' => $project->id,
                    'no' => "{$prefix}-FR-{$relation['functionalRequirementNo']}",
                ])->first()->id;
                $rtm->testCaseId = TestCase::where([
                    'projectId' => $project->id,
                    'no' => "{$prefix}-TC-{$testCaseNo}",
                ])->first()->id;
                $rtm->save();
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
                RequirementTraceabilityMatrix::where('projectId', $project->id)->delete();
            }
            else {
                $frId = FunctionalRequirement::where([
                    ['no', $functionalRequirementNo],
                    ['projectId', $project->id]
                ])->first()->id;
                RequirementTraceabilityMatrix::where('frId', $frId)->delete();
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
             return response()->json(['msg' => 'Internal Server Error.'],500);
        }
        return response()->json(['msg' => 'Delete Requirement Traceability Matrix.'],200);
    }
}
