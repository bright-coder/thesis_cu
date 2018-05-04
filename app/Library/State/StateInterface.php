<?php

namespace App\Library\State;

use App\Model\ChangeRequestInput;

interface StateInterface
{

    public function analyze(ChangeRequestInput $changeRequestInput): array;
    public function getStateName(): string;
    public function nextState();

}
