<?php

namespace App\Library\State;

use App\Library\State\ChangeAnalysis;

interface StateInterface
{

    public function process(ChangeAnalysis $changeAnalysis): bool;


}
