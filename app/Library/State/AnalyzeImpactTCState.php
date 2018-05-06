<?php

namespace App\Library\State;

use App\Library\State\StateInterface;
use App\Library\State\AnalyzeImpactRTMState;
use App\Model\ChangeRequest;
use App\Model\ChangeRequestInput;
use App\Model\TestCase;
use App\Model\TestCaseInput;
use App\Model\RequirementTraceabilityMatrix;
use App\Model\RequirementTraceabilityMatrixRelation;
use DB;

class AnalyzeImpactTCState implements StateInterface
{
    public function nextState()
    {
        return new AnalyzeImpactRTMState();
    }

    public function getStateName(): String
    {
        return 'AnalyzeImpactTCState';
    }

    public function analyze(ChangeReqeustInput $changeRequestInput): void
    {
        $frInputImpactResult = DB::table('FUNCTIONAL_REQUIREMENT_INPUT_IMPACT')->where('changeRequestInputId', $changeRequestInput->id)-get();
        $projectId = ChangeRequest::find($changeRequestInput->changeRequestId)->projectId;
        $rtmId = RequirementTraceabilityMatrix::where('projectId', $projectId)->first();
        foreach($frInputImpactResult as $frInput) {
            $frId = $frInput->functionalRequirementId;
            $testCaseIdList = RequirementTraceabilityMatrixRelation::where([
                ['requirementTraceabilityMatrixId', $rtmId],
                ['functionalRequirementId', $frId]
            ])->get();
            foreach($testCaseIdList as $testCaseId) {
                if()
            }
        }

    }
}
