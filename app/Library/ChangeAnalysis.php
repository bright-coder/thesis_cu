<?php

namespace App\Library;

use App\Library\State\StateInterface;
use App\Library\State\AnalyzeImpactDBState;
use App\Library\State\AnalyzeImpactFRState;
use App\Library\State\AnalyzeImpactTCState;
use App\Library\State\AnalyzeImpactRTMState;
use App\Model\ChangeRequest;
use App\Model\ChangeRequestInput;

class ChangeAnalysis
{
    private $projectId;
    private $changeRequest;
    private $changeRequestInputList;

    private $dbImpactResult = [];
    private $frImpactResult = [];
    private $tcImpactResult = [];
    private $rtmImpactResult = [];

    private $state;


    public function __construct(string $projectId, ChangeRequest $changeRequest, array $changeRequestInputList)
    {
        $this->projectId = $projectId;
        $this->changeRequest = $changeRequest;
        $this->changeRequestInputList = $changeRequestInputList;
        $this->state = new AnalyzeImpactDBState;
    }

    public function getChangeFunctionalRequirementId(): string
    {
        return $this->changeRequest->changeFunctionalRequirementId;
    }

    public function addDBImpactResult(string $changeRequestInputId, array $schemaImpactResult, array $instanceImpactResult) : void
    {
        $this->dbImpactResult[$changeRequestInputId] = [
            'schema' => $schemaImpactResult,
            'instance' => $instanceImpactResult
        ];
    }

    public function setTcImpactResult(array $tcImpactResult): void
    {
        $this->tcImpactResult = $tcImpactResult;
    }

    public function getTcImpactResult(): array 
    {
        return $this->tcImpactResult;
    }

    public function getDBImpactResult(): array {
        return $this->dbImpactResult;
    }

    public function setFRImpactResult(array $frImpactResult) : void
    {
        $this->frImpactResult = $frImpactResult;
    }

    public function getFrImpactResult(): array {
        return $this->frImpactResult;
    }

    public function setRtmImpactResult(array $rtmImpactResult): void
    {
        $this->rtmImpactResult = $rtmImpactResult;
    }

    public function getRtmImpactResult(): array
    {
        return $this->rtmImpactResult;
    }


    public function addInstanceImpact(string $changeRequestInputId, array $impactResult) : void
    {
        $this->instanceImpactResult[$changeRequestInputId] = $impactResult;
    }

    public function isConsistent() : bool
    {
    }

    public function getChangeRequest() : changeRequest
    {
        return $this->changeRequest;
    }

    public function getAllChangeRequestInput() : array
    {
        return $this->changeRequestInputList;
    }

    public function getProjectId() : string
    {
        return $this->projectId;
    }

    public function setState(StateInterface $state): void
    {
        $this->state = $state;
    }

    public function analyze(): void
    {
        $this->state->analyze($this);


        // foreach ($this->changeRequestInputList as $changeRequestInput) {
        //     if ($changeRequestInput->status == 'imported') {
        //         $state = new AnalyzeImpactDBState();
        //     } elseif ($changeRequestInput->status == 'dbAnalyzed') {
        //         $state = new AnalyzeImpactFRState();
        //     } elseif ($changeRequestInput->status == 'frAnalyzed') {
        //         $state = new AnalyzeImpactTCstate();
        //     } elseif ($changeRequestInput->status == 'tcAnalyzed') {
        //         $state = new AnalyzeImpactRTMstate();
        //     } else {
        //         continue;
        //     }
            
        //     do {
        //         $result = $state->analyze($changeRequestInput);
        //         $this->setResult($changeRequestInput->id, $result, $state->getStateName());
        //         $state = $state->nextState();
        //     } while ($state->nextState() !== null);
        // }
    }

}
