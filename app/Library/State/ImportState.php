<?php

namespace App\Library\State;

use App\ChangeRequest;
use App\ChangeRequestInput;
use App\FunctionalRequirement;
use App\FunctionalRequirementInput;
use App\Library\State\AnalyzeImpactDBState;
use App\Library\State\ChangeAnalysis;
use App\Library\State\StateInterface;
use App\Project;
use App\RequirementTraceabilityMatrix;
use App\RequirementTraceabilityMatrixRelation;
use App\TestCase;
use App\testCaseInput;
use DB;

class ImportState implements StateInterface
{
    public function getStateNo() : int {
        return 1;
    }

    public function process(ChangeAnalysis $changeAnalysis): bool
    {

        $request = $changeAnalysis->getRequest();

        DB::beginTransaction();
        try {

            $project = new Project;
            $project->id = $request['projectInfo']['id'];
            $project->dbHostName = $request['connectDbInfo']["hostName"];
            $project->dbName = $request['connectDbInfo']["dbName"];
            $project->dbUsername = $request['connectDbInfo']["username"];
            $project->dbPassword = $request['connectDbInfo']["password"];
            $project->dbPort = $request['connectDbInfo']["port"];
            $project->dbType = $request['connectDbInfo']["type"];
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
            $changeRequest->status = "1";
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
            $changeAnalysis->setProjectId((int) $request['projectInfo']['id']);
            $changeAnalysis->setChangeRequestId((int) $changeRequest->id);
            $changeAnalysis->setMessage("INSERT SUCCESS");
            $changeAnalysis->setStatusCode(201);
            $changeAnalysis->setState(new AnalyzeImpactDBState);
            //$changeAnalysis->

        } catch (\Exception $e) {
            DB::rollBack();
            $changeAnalysis->setMessage($e->getMessage());
            $changeAnalysis->setStatusCode(303);
            return false;
        }
        return true;
    }

}
