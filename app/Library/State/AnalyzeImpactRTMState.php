<?php

namespace App\Library\State;

use App\Library\State\StateInterface;

class AnalyzeImpactRTMState implements StateInterface
{
    public function nextState()
    {
        return NULL;
    }

    public function getStateName(): String
    {
        return 'AnalyzeImpactTCState';
    }
}
