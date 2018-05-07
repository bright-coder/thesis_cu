<?php

namespace App\Library\State;

use App\Library\State\StateInterface;
use App\Library\ChangeAnalysis;
use App\Model\FunctionalRequirement;
use App\Model\RequirementTraceabilityMatrix;
use App\Model\RequirementTraceabilityMatrixRelation;

class AnalyzeImpactRTMState implements StateInterface
{
    private $rtmImpactResult = [];

    public function getStateName(): String
    {
        return 'AnalyzeImpactRTMState';
    }

    public function analyze(ChangeAnalysis $changeAnalysis): void
    {   
        $projectId = $changeAnalysis->getProjectId();
        $rtmId = RequirementTraceabilityMatrix::where('projectId', $projectId)->id;
        $tcImpactResult = $changeAnalysis->getcImpactResult();
        foreach($tcImpactResult as $tcImpact) {
            $frNo = FunctionalRequirement::find($tcImpact['functionalRequirementId'])->no;

            if($tcImpact->changeType == 'delete') {
                $rtmRelation = RequirementTraceabilityMatrixRelation::where([
                    ['functionalRequirementId', $tcImpact['functionalRequirementId']],
                    ['requirementTraceabilityMatrixId', $rtmId],
                    ['testCaseId', $tcImpact['id']]
                ])->first();
                $rtmImpactResult[] = [
                    'id' => $rtmRelation->id,
                    'changeType' => 'delete',
                    'functionalRequirementId' => $tcImpact['functionalRequirementId'],
                    'functionalRequirementNo' => $frNo,
                    'testCaseId' => $tcImpact['id'],
                    'testCaseNo' => $tcImpact['no']
                ];
                $rtmRelation->activeFlag = "N";
                $rtmRelation->save();

            }
            elseif($tcImpact->changeType == 'add') {
                $rtmRelation = new RequirementTraceabilityMatrixRelation;
                $rtmRelation->functionalRequirementId = $tcImpact['functionalRequirementId'];
                $rtmRelation->requirementTraceabilityMatrixId = $rtmId;
                $rtmRelation->testCaseId = $tcImpact['id'];
                $rtmRelation->activeFlag = "Y";
                $rtmRelation->save();

                $rtmImpactResult[] = [
                    'id' => $rtmRelation->id,
                    'changeType' => 'add',
                    'functionalRequirementId' => $tcImpact['functionalRequirementId'],
                    'functionalRequirementNo' => $frNo,
                    'testCaseId' => $tcImpact['id'],
                    'testCaseNo' => $tcImpact['no']
                ];
            }
            
        }

        $changeAnalysis->setRtmImpactResult($this->rtmImpactResult);
    }
}
