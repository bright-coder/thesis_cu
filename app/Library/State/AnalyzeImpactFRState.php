<?php

namespace App\Library\State;

use App\Library\State\StateInterface;
use App\Library\State\AnalyzeImpactTCState;
use App\Model\ChangeRequestInput;

class AnalyzeImpactFRState implements StateInterface
{
    public function nextState() {
        return new AnalyzeImpactTCState();
    }

    public function getStateName(): String{
        return 'AnalyzeImpactFRState';
    }

    public function analyze(ChangeRequestInput $changeRequestInput): array {
        
    }
}
