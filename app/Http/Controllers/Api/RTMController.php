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

        //$result = [];
        //$statusCode = 202;
        $rtm = RequirementTraceabilityMatrix::where('projectId', $project->id)
            ->orderBy('frId', 'asc')->orderBy('tcId', 'asc')->get();
        if(count($rtm) == 0) {
            return response()->json("Not found your RTM", 202);
        }
        $result = [];

        foreach($rtm as $relation) {
            if(!isset($result[FunctionalRequirement::where('id', $relation->frId)->first()->no])) {
                $result[FunctionalRequirement::where('id', $relation->frId)->first()->no] = [];
            }

            $result[FunctionalRequirement::where('id', $relation->frId)->first()->no][] = 
                TestCase::where('id', $relation->tcId)->first()->no;

        }
        $resultNewKey = [];
        foreach($result as $frNo => $tcNoList) {
            $resultNewKey[] =  ['frNo' => $frNo, 'tcNoList' => $tcNoList];
        }


        return response()->json($resultNewKey, 200);
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
        $prefix = $project->prefix;
        foreach ($request as $relation) {
            foreach ($relation['testCaseNos'] as $testCaseNo) {
                $rtm = new RequirementTraceabilityMatrix;
                $rtm->projectId = $project->id;
                $rtm->frId = FunctionalRequirement::where([
                    'projectId' => $project->id,
                    'no' => "{$prefix}-FR-{$relation['functionalRequirementNo']}",
                ])->first()->id;
                $rtm->tcId = TestCase::where([
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
