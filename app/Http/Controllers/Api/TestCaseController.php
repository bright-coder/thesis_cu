<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TestCaseRequest;
use App\Library\GuardProject;
use DB;
use Illuminate\Http\Request;
use App\Model\TestCase;
use App\Model\TestCaseInput;
use App\Model\Project;

class TestCaseController extends Controller
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
    public function store(TestCaseRequest $request, $projectName)
    {
        $guard = new GuardProject($request->bearerToken());
        $project = $guard->getProject($projectName);

        if (!$project) {
            return response()->json(['msg' => 'Bad Request'], 400);
        }

        $request = $request->json()->all();
        DB::beginTransaction();
        try {
            $prefix = Project::find($project->id)->prefix;
            foreach ($request as $importTc) {
                $testCase = new TestCase;
                $testCase->projectId = $project->id;
                $testCase->no = "{$prefix}-TC-{$importTc['no']}";
                $testCase->type = $importTc['type'];
                $testCase->activeFlag = 'Y';
                $testCase->save();
                foreach ($importTc['inputs'] as $importTcInput) {
                    $testCaseInput = new testCaseInput;
                    $testCaseInput->testCaseId = $testCase->id;
                    $testCaseInput->name = $importTcInput['name'];
                    $testCaseInput->testData = $importTcInput['testData'];
                    $testCaseInput->save();
                }
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['msg' => "Internal Sever Error."], 500);
        }
        return response()->json(['msg' => "Insert Success."], 200);
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
    public function destroy(Request $request, $projectName, $testCaseNo = "all")
    {
        $guard = new GuardProject($request->bearerToken());
        $project = $guard->getProject($projectName);

        if (!$project) {
            return response()->json(['msg' => 'Bad Request'], 400);
        }

        DB::beginTransaction();
        try {
            if ($testCaseNo === "all") {
                $tcs = TestCase::select('id')->where('projectId', $project->id)->get();
                foreach ($tcs as $tc) {
                    TestCaseInput::where('testCaseId', $tc->id)->delete();
                    TestCase::where('id', $tc->id)->delete();
                }
            }
            else {
                $testCaseId = TestCase::where([
                    ['no', $testCaseNo],
                    ['projectId', $project->id]
                ])->first()->id;
                TestCaseInput::where('testCaseId', $testCaseId)->delete();
                TestCase::where('id', $testCaseId)->delete();
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
             return response()->json(['msg' => 'Internal Server Error.'],500);
        }
        return response()->json(['msg' => 'Delete Test Case success.'],200);
        
    }
}
