<?php

namespace App\Http\Controllers;

use App\FunctionalRequirement;
use App\FunctionalRequirementInput;
use App\Project;
use App\TestCase;
use App\testCaseInput;
use App\RequirementTraceabilityMatrix;
use App\RequirementTraceabilityMatrixRelation;
use App\ChangeRequest;
use App\ChangeRequestInput;
use DB;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return response()->json(['method' => 'get']);
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
    public function store(Request $request)
    {

        $data = $request->json()->all(); // jsonObject to Array;
        /**
         * example { "msg" : "test message" } => ['msg']
         */
        /**
         *  USE SQL => DELETE FROM 'table' instead of TRUNCATE ;
         */

        DB::beginTransaction();
        try {

            $project = new Project;
            $project->id = $request['projectInfo']['id'];
            $project->dbHostName = $request['connectDbInfo']["hostName"];
            $project->dbName = $request['connectDbInfo']["dbName"];
            $project->dbUsername = $request['connectDbInfo']["username"];
            $project->dbPassword = $request['connectDbInfo']["password"];
            $project->dbPort = $request['connectDbInfo']["port"];
            $project->save();

            foreach ($request['functionalRequirements'] as $importFr) {
                $functionalRequirement = new FunctionalRequirement;
                $functionalRequirement->projectId = $request['projectInfo']['id'];
                $functionalRequirement->no = $importFr['no'];
                $functionalRequirement->description = $importFr['description'];
                $functionalRequirement->version = $importFr['version'];
                $functionalRequirement->save();

                foreach ($importFr['inputs'] as $importFrInput) {
                    $functionalRequirementInput = new FunctionalRequirementInput;
                    $functionalRequirementInput->functionalRequirementId = $functionalRequirement->id;
                    $functionalRequirementInput->name = $importFrInput['name'];
                    $functionalRequirementInput->dataType = $importFrInput['dataType'];
                    $functionalRequirementInput->length = $importFrInput['length'];
                    $functionalRequirementInput->scale = $importFrInput['scale'];
                    $functionalRequirementInput->default = $importFrInput['default'];
                    $functionalRequirementInput->unique = $importFrInput['unique'];
                    $functionalRequirementInput->nullable = $importFrInput['nullable'];
                    $functionalRequirementInput->min = $importFrInput['min'];
                    $functionalRequirementInput->max = $importFrInput['max'];
                    $functionalRequirementInput->tableName = $importFrInput['tableName'];
                    $functionalRequirementInput->columnName = $importFrInput['columnName'];
                    $functionalRequirementInput->save();
                }
            }

            foreach ($request['testCases'] as $importTc) {
                $testCase = new TestCase;
                $testCase->projectId = $request['projectInfo']['id'];
                $testCase->no = $importTc['no'];
                $testCase->type = $importTc['type'];
                $testCase->version = $importTc['version'];
                $testCase->save();

                foreach ($importTc['inputs'] as $importTcInput) {
                    $testCaseInput = new testCaseInput;
                    $testCaseInput->testCaseId = $testCase->id;
                    $testCaseInput->name = $importTcInput['name'];
                    $testCaseInput->data = $importTcInput['data'];
                    $testCaseInput->save();
                }

            }

            $requirementTraceabilityMatrix = new RequirementTraceabilityMatrix;
            $requirementTraceabilityMatrix->projectId = $request['projectInfo']['id'];
            $requirementTraceabilityMatrix->version = $request['requirementTraceabilityMatrix']['version'];
            $requirementTraceabilityMatrix->save();
            foreach ($request['requirementTraceabilityMatrix']['relations'] as $importRTMrelation) {
                foreach ($importRTMrelation['testCaseNos'] as $testCaseNo) {
                    $requirementTraceabilityMatrixRelation = new RequirementTraceabilityMatrixRelation;

                    $requirementTraceabilityMatrixRelation->requirementTraceabilityMatrixId = $requirementTraceabilityMatrix->id;
                    $requirementTraceabilityMatrixRelation->functionalRequirementId = FunctionalRequirement::where([
                        'projectId' => $request['projectInfo']['id'],
                        'no' => $importRTMrelation['functionalRequirementNo'],
                    ])->first()->id;
                    $requirementTraceabilityMatrixRelation->testCaseId = TestCase::where([
                        'projectId' => $request['projectInfo']['id'],
                        'no' => $testCaseNo,
                    ])->first()->id;
                    $requirementTraceabilityMatrixRelation->save();
                }
            }

            $changeRequest = new ChangeRequest;
            $changeRequest->projectId = $request['projectInfo']['id'];
            $changeRequest->changeFunctionalRequirementId = FunctionalRequirement::where([
                'projectId' => $request['projectInfo']['id'],
                'no' => $request['changeRequest']['functionalRequirementNo'],
            ])->first()->id;
            $changeRequest->status = "GET";
            $changeRequest->callType = $request['changeRequest']['callType'];
            $changeRequest->save();
            foreach ($request['changeRequest']['inputs'] as $changeInput) {
                $changeRequestInput = new ChangeRequestInput;
                $changeRequestInput->changeRequestId = $changeRequest->id;
                $changeRequestInput->changeType = $changeInput['changeType'];
                $changeRequestInput->name = $changeInput['name'];
                $changeRequestInput->dataType = $changeInput['dataType'];
                $changeRequestInput->length = $changeInput['length'];
                $changeRequestInput->scale = $changeInput['scale'];
                $changeRequestInput->default = $changeInput['default'];
                $changeRequestInput->unique = $changeInput['unique'];
                $changeRequestInput->nullable = $changeInput['nullable'];
                $changeRequestInput->min = $changeInput['min'];
                $changeRequestInput->max = $changeInput['max'];
                $changeRequestInput->tableName = $changeInput['tableName'];
                $changeRequestInput->columnName = $changeInput['columnName'];
                $changeRequestInput->save();
            }



            DB::commit();
            $msg = "INSERT SUCCESS";
            $statusCode = 201;

        } catch (\Exception $e) {
            DB::rollBack();
            $msg = $e->getMessage();
            $statusCode = 303;
        }

        // dd(json_decode($request->getContent(), true));
        return response()->json(['msg' => $msg], $statusCode);
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
        return response()->json(['method' => "get {$id}"]);
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
