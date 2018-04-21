<?php

namespace App\Library\State;

use App\Library\ChangeAnalysis;

interface StateInterface
{

    public function analyze(ChangeAnalysis $changeAnalysis);

}
