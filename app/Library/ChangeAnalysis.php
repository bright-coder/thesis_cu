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

    public function __construct(string $projectId, ChangeRequest $changeRequest, array $changeRequestInputList)
    {
        $this->projectId = $projectId;
        $this->changeRequest = $changeRequest;
        $this->changeRequestInputList = $changeRequestInputList;
    }

    public function addDBImpactResult(string $changeRequestInputId, array $schemaImpactResult, array $instanceImpactResult) : void
    {
        $this->dbImpactResult[$changeRequestInputId] = [
            'schema' => $schemaImpactResult,
            'instance' => $instanceImpactResult
        ];
    }

    public function addFRImpactResult(string $changeRequestInputId, array $schemaImpactResult) : void
    {
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

    public function analyze(): void
    {
        foreach ($this->changeRequestInputList as $changeRequestInput) {
            if ($changeRequestInput->status == 'imported') {
                $state = new AnalyzeImpactDBState();
            } elseif ($changeRequestInput->status == 'dbAnalyzed') {
                $state = new AnalyzeImpactFRState();
            } elseif ($changeRequestInput->status == 'frAnalyzed') {
                $state = new AnalyzeImpactTCstate();
            } elseif ($changeRequestInput->status == 'tcAnalyzed') {
                $state = new AnalyzeImpactRTMstate();
            } else {
                continue;
            }
            
            do {
                $result = $state->analyze($changeRequestInput);
                $this->setResult($changeRequestInput->id, $result, $state->getStateName());
                $state = $state->nextState();
            } while ($state->nextState() !== null);
        }
    }

    public function setResult(ChangeRequestInput $changeRequestInput, array $result, string $fromState): void
    {
        switch ($fromState) {
            case 'AnalyzeImpactDBState':
                //ChangeRequest::
                $this->dbImpactResult[$changeRequestInput->id] = $result;
                $changeRequestInput->status = 'dbAnalyzed';
                break;
            case 'AnalyzeImpactFRState':
                $this->frImpactResult[$changeRequestInput->id] = $result;
                $changeRequestInput->status = 'frAnalyzed';
                break;
            case 'AnalyzeImpactTCstate':
                $this->tcImpactResult[$changeRequestInput->id] = $result;
                $changeRequestInput->status = 'tcAnalyzed';
                break;
            case 'AnalyzeImpactRTMstate':
                $this->rtmImpactResult[$changeRequestInput->id] = $result;
                $changeRequestInput->status = 'rtmAnalyzed';
                break;
            default:
                # code...
                break;
        }
        $changeRequestInput->save();
    }
}
