<?php

namespace App\Library\State;

use App\Library\State\StateInterface;
use App\Library\ChangeAnalysis;
use App\Model\FunctionalRequirement;
use App\Model\RequirementTraceabilityMatrix;
use App\Model\TestCase;

class AnalyzeImpactRTMState implements StateInterface
{

    public function getStateName(): String
    {
        return 'AnalyzeImpactRTMState';
    }

    public function analyze(ChangeAnalysis $changeAnalysis): void
    {   
        $projectId = $changeAnalysis->getProjectId();
        $tcImpactResult = $changeAnalysis->getTcImpactResult();
        $result = [];
        foreach($tcImpactResult as $tcNo => $tcInfo) {
            if($tcInfo['changeType'] == 'add') {
                $rtm = new RequirementTraceabilityMatrix;
                $rtm->projectId = $projectId;
                $rtm->tcId = $tcInfo['tcId'];
                $rtm->frId = $tcInfo['frId'];
                $rtm->save();
                $frNo = FunctionalRequirement::where('id', $tcInfo['frId'])->first()->no;
                if(!isset($result[$frNo])) {
                    $result[$frNo] = [];
                }
                $result[$frNo][$tcNo] = 'add';
            }
            elseif($tcInfo['changeType'] == 'delete') {
                $tcId = $tcInfo['tcId'];
                $rtm = RequirementTraceabilityMatrix::where([
                    ['projectId', $projectId],
                    ['tcId', $tcId],
                    ['frId', $tcInfo['frId']]
                ])->delete();
                $frNo = FunctionalRequirement::where('id', $tcInfo['frId'])->first()->no;
                if(!isset($result[$frNo])) {
                    $result[$frNo] = [];
                }
                $result[$frNo][$tcNo] = 'delete';
            }
        }
        
        $changeAnalysis->addRtmImpactResult($result);
        $changeAnalysis->saveRtmRelationImpact();
    }

}
