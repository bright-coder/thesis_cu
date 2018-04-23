<?php

namespace App\Library;

use App\Library\State\StateInterface;
use App\Library\State\AnalyzeImpactDBState;
use App\Library\State\AnalyzeImpactFRState;
use App\Library\State\AnalyzeImpactTCState;
use App\Library\State\AnalyzeImpactRTMState;
use App\ChangeRequest;
use App\ChangeRequestInput;

class ChangeAnalysis
{

    private $projectId;
    private $changeRequest;
    private $changeRequestInputList;
    
    private $state;

    public function __construct(string $projectId, ChangeRequest $changeRequest, array $changeRequestInputList) {
        $this->projectId = $projectId;
        $this->changeRequest = $changeRequest;
        $this->changeRequestInputList = $changeRequestInputList;

    }

    public function isConsistent() : bool {

    }

    public function setState(StateInterface $state) {
        $this->state = $state;
    }

    public function getChangeRequest() : changeRequest {
        return $this->changeRequest;
    }

    public function getAllChangeRequestInput() : array {
        return $this->changeRequestInputList;
    }

    public function getProjectId() : string {
        return $projectId;
    }

    public function analyze() {

        if($this->changeRequest->status == 'imported')
        {
            $this->setState(new AnalyzeImpactDBState());
        }
        else if($this->changeRequest->status == 'dbAnalyzed')
        {
            $this->setState(new AnalyzeImpactFRState());
        }
        else if($this->changeRequest->status == 'FRAnalyzed')
        {
            $this->setState(new AnalyzeImpactTCState());
        }
        else if($this->changeRequest->status == 'TCAnalyzed')
        {
            $this->setState(new AnalyzeImpactRTMState());
        }
        
        $this->state->analyze($this);
    
    }

}
