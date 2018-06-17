<?php

namespace App\Library\State;

use App\Library\State\StateInterface;
use App\Library\ChangeAnalysis;
use App\Model\FunctionalRequirement;
use App\Model\RequirementTraceabilityMatrix;
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
        $result = [];
        foreach($tcImpactResult as $tcNo => $tcInfo) {
            if($tcInfo['changeType'] == 'add') {
                $rtm = new RequirementTraceabilityMatrix;
                $rtm->projectId = $projectId;
                $rtm->tcId = TestCase::where([
                    ['projectId', $projectId],
                    ['no', $tcNo]
                ])->first()->id;
                $rtm->frId = $tcInfo['frId'];
                $rtm->save();
                $frNo = FunctionalRequirement::where('id', $tcInfo['frId'])->first()->no;
                if(!isset($rtm[$frNo])) {
                    $rtm[$frNo] = [];
                }
                $rtm[$frNo][$tcNo] = 'add';
            }
            elseif($tcInfo['changeType'] == 'delete') {
                $tcId = TestCase::where([
                    ['projectId', $projectId],
                    ['no', $tcNo]
                ])->first()->id;
                $rtm = RequirementTraceabilityMatrix::where([
                    ['projectId', $projectId],
                    ['tcId', $tcId],
                    ['frId', $frId]
                ])->delete();
                if(!isset($rtm[$frNo])) {
                    $rtm[$frNo] = [];
                }
                $rtm[$frNo][$tcNo] = 'delete';
            }
        }
        $changeAnalysis->addRtmImpactResult($this->rtmImpactResult);
    }

}
