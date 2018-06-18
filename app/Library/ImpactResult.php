<?php

namespace App\Library;

use App\Model\ColumnImpact;
use App\Model\InstanceImpact;
use App\Model\OldInstance;
use App\Model\FrImpact;
use App\Model\FrInputImpact;
use App\Model\TcImpact;
use App\Model\TcInputImpact;
use App\Model\TestCase;
use App\Model\RtmRelationImpact;
use App\Model\ChangeRequestInput;

class ImpactResult
{
    private $changeRequestId;

    public function __construct($changeRequestId)
    {
        $this->changeRequestId = $changeRequestId;
    }

    public function getImpact(): array
    {
        return [
            'schema' => $this->getSchemaImpact(),
            'instance' => $this->getInstanceImpact(),
            'keys' => $this->getKeyImpact(),
            'functionalRequirments' => $this->getFrImpact(),
            'testCases' => $this->getTcImpact(),
            'rtm' => $this->getRtmImpact()
        ];
    }

    private function getSchemaImpact(): array
    {
        
    }

    private function getInstanceImpact(): array
    {
       
    }

    private function getFrImpact(): array
    {
       
    }

    private function getTcImpact(): array
    {
        
    }

    private function getRtmImpact() : array
    {
        
    }

    private function getKeyImpactResult(): array 
    {
        return [];
    }
}
