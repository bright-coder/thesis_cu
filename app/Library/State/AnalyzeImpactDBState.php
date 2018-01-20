<?php

namespace App\Library\State;

use App\Library\State\StateInterface;

use App\Library\FunctionalRequirement\FR;

class AnalyzeImpactDBState implements StateInterface
{
    public function construct(){
        $fr = new FR();
        
    }
}
