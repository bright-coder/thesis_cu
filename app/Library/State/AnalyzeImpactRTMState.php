<?php

namespace App\Library\State;

use App\Library\State\StateInterface;
use App\Library\ChangeAnalysis;
use App\Model\FunctionalRequirement;
use App\Model\RequirementTraceabilityMatrix;
use App\Model\RequirementTraceabilityMatrixRelation;
use App\Model\TestCase;

class AnalyzeImpactRTMState implements StateInterface
{
    private $rtmImpactResult = [];
    private $rtmId;
    private $projectId;

    public function getStateName(): String
    {
        return 'AnalyzeImpactRTMState';
    }

    public function analyze(ChangeAnalysis $changeAnalysis): void
    {   
        $projectId = $changeAnalysis->getProjectId();
        $this->projectId = $changeAnalysis->getProjectId();
        $this->rtmId = RequirementTraceabilityMatrix::where('projectId', $projectId)->first()->id;
        $tcImpactResult = $changeAnalysis->getTcImpactResult();
        foreach($tcImpactResult as $tcImpact) {
            $frNo = FunctionalRequirement::find($tcImpact['functionalRequirementId'])->no;

            if($tcImpact['changeType'] == 'delete') {
                // $rtmRelation = RequirementTraceabilityMatrixRelation::where([
                //     ['functionalRequirementId', $tcImpact['functionalRequirementId']],
                //     ['requirementTraceabilityMatrixId', $rtmId],
                //     ['testCaseId', $tcImpact['oldTc']]
                // ])->first();
                $this->rtmImpactResult[] = [
                    //'id' => $rtmRelation->id,
                    'changeType' => 'delete',
                    'functionalRequirementId' => $tcImpact['functionalRequirementId'],
                    'functionalRequirementNo' => $frNo,
                    //'testCaseId' => $tcImpact['oldTcId'],
                    'testCaseNo' => TestCase::where('id', $tcImpact['oldTcId'])->first()->no
                ];
                //$rtmRelation->activeFlag = "N";
                //$rtmRelation->save();

            }
            elseif($tcImpact['changeType'] == 'add') {
                //dd($tcImpact['newNo']);
                $this->rtmImpactResult[] = [
                    //'id' => $rtmRelation->id,
                    'changeType' => 'add',
                    'functionalRequirementId' => $tcImpact['functionalRequirementId'],
                    'functionalRequirementNo' => $frNo,
                    // 'testCaseId' => TestCase::where([
                    //     ['projectId', $changeAnalysis->getProjectId()],
                    //     ['no', $tcImpact['newNo']]
                    // ])->first()->id,
                    'testCaseNo' => $tcImpact['newNo']
                ];
            }
            
        }
        $changeAnalysis->setRtmImpactResult($this->rtmImpactResult);
        //dd($changeAnalysis->getRtmImpactResult());
        $this->modify();
    }

    private function modify() {
        $rtmId = $this->rtmId;
        foreach($this->rtmImpactResult as $rtmImpact) {
            $testCaseId = TestCase::where([
                ['projectId', $this->projectId],
                ['no', $rtmImpact['testCaseNo']]
            ])->first()->id;
            
            if($rtmImpact['changeType'] == 'add') {
                $newRelation = new RequirementTraceabilityMatrixRelation;
                $newRelation->requirementTraceabilityMatrixId = $rtmId;
                $newRelation->functionalRequirementId = $rtmImpact['functionalRequirementId'];
                $newRelation->testCaseId = $testCaseId;
                $newRelation->activeFlag = 'Y';
                $newRelation->save();
            }
            else {
                RequirementTraceabilityMatrixRelation::where([
                    ['requirementTraceabilityMatrixId', $rtmId],
                    ['functionalRequirementId', $rtmImpact['functionalRequirementId']],
                    ['testCaseId', $testCaseId],
                    ['activeFlag', 'Y']
                ])->update(['activeFlag' => 'N']);
            }
        }
    }
}
