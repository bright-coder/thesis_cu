<?php

namespace App\Library;

use App\Library\State\StateInterface;
use App\Library\State\AnalyzeImpactDBState;
use App\Library\State\AnalyzeImpactFRState;
use App\Library\State\AnalyzeImpactTCState;
use App\Library\State\AnalyzeImpactRTMState;
use App\Model\ChangeRequest;
use App\Model\ChangeRequestInput;
use App\Model\TableImpact;
use App\Model\ColumnImpact;
use App\Model\InstanceImpact;
use App\Model\OldInstance;

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

    public function getDBImpactResult(): array
    {
        return $this->dbImpactResult;
    }

    public function setFRImpactResult(array $frImpactResult) : void
    {
        $this->frImpactResult = $frImpactResult;
    }

    public function getFrImpactResult(): array
    {
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
    }

    public function saveSchemaImpact()
    {
        $changeRequestId = $this->getChangeRequest()->id;
        $tableImpactMem = [];
        foreach ($this->dbImpactResult as $dbImpactList) {
            foreach ($dbImpactList['schema'] as $schema) {
                if (!array_key_exists($schema['tableName'], $tableImpact)) {
                    $newTableImpact = new TableImpact;
                    $newTableImpact->name = $schema['tableName'];
                    $newTableImpact->changeRequestId = $changeRequestId;
                    $newTableImpact->save();

                    $tableImpactMem[ $schema['tableName'] ] = $newTableImpact->id;
                }
                if ($schema['changeType'] == 'add') {
                    $newColumnImpact = new ColumnImpact;
                    $newColumnImpact->name = $schema['columnName'];
                    $newColumnImpact->tableImpactId = $tableImpactMem[$schema['tableName']];
                    $newColumnImpact->changeType = 'add';
                    $newColumnImpact->versionType = 'new';
                    if (array_key_exists('dataType', $schema['newSchema'])) {
                        $newColumnImpact->dataType = $schema['newSchema']['dataType'];
                    }
                    if (array_key_exists('length', $schema['newSchema'])) {
                        $newColumnImpact->length = $schema['newSchema']['length'];
                    }
                    if (array_key_exists('precision', $schema['newSchema'])) {
                        $newColumnImpact->precision = $schema['newSchema']['precision'];
                    }
                    if (array_key_exists('scale', $schema['newSchema'])) {
                        $newColumnImpact->scale = $schema['newSchema']['scale'];
                    }
                    if (array_key_exists('default', $schema['newSchema'])) {
                        $newColumnImpact->default = $schema['newSchema']['default'];
                    }
                    if (array_key_exists('nullable', $schema['newSchema'])) {
                        $newColumnImpact->nullable = $schema['newSchema']['nullable'];
                    }
                    if (array_key_exists('unique', $schema['newSchema'])) {
                        $newColumnImpact->unique = $schema['newSchema']['unique'];
                    }
                    if (array_key_exists('min', $schema['newSchema'])) {
                        $newColumnImpact->min = $schema['newSchema']['min'];
                    }
                    if (array_key_exists('max', $schema['newSchema'])) {
                        $newColumnImpact->max = $schema['newSchema']['max'];
                    }
                    $newColumnImpact->save();
                } elseif ($schema['changeType'] == 'edit') {
                    $newColumnImpact = new ColumnImpact;
                    $newColumnImpact->name = $schema['columnName'];
                    $newColumnImpact->tableImpactId = $tableImpactMem[$schema['tableName']];
                    $newColumnImpact->changeType = 'edit';
                    $newColumnImpact->versionType = 'old';
                    $newColumnImpact->length = $schema['oldSchema']['length'];
                    $newColumnImpact->precision = $schema['oldSchema']['precision'];
                    $newColumnImpact->scale = $schema['oldSchema']['scale'];
                    $newColumnImpact->default = $schema['oldSchema']['default'];
                    $newColumnImpact->nullable = $schema['oldSchema']['nullable'] ? 'Y' : 'N';
                    $newColumnImpact->unique = $schema['oldSchema']['unique'] ? 'Y' : 'N';
                    $newColumnImpact->min = $schema['oldSchema']['min'];
                    $newColumnImpact->max = $schema['oldSchema']['max'];
                    $newColumnImpact->save();

                    $newColumnImpact = new ColumnImpact;
                    $newColumnImpact->name = $schema['columnName'];
                    $newColumnImpact->tableImpactId = $tableImpactMem[$schema['tableName']];
                    $newColumnImpact->changeType = 'edit';
                    $newColumnImpact->versionType = 'new';
                    if (array_key_exists('dataType', $schema['newSchema'])) {
                        $newColumnImpact->dataType = $schema['newSchema']['dataType'];
                    }
                    if (array_key_exists('length', $schema['newSchema'])) {
                        $newColumnImpact->length = $schema['newSchema']['length'];
                    }
                    if (array_key_exists('precision', $schema['newSchema'])) {
                        $newColumnImpact->precision = $schema['newSchema']['precision'];
                    }
                    if (array_key_exists('scale', $schema['newSchema'])) {
                        $newColumnImpact->scale = $schema['newSchema']['scale'];
                    }
                    if (array_key_exists('default', $schema['newSchema'])) {
                        $newColumnImpact->default = $schema['newSchema']['default'];
                    }
                    if (array_key_exists('nullable', $schema['newSchema'])) {
                        $newColumnImpact->nullable = $schema['newSchema']['nullable'];
                    }
                    if (array_key_exists('unique', $schema['newSchema'])) {
                        $newColumnImpact->unique = $schema['newSchema']['unique'];
                    }
                    if (array_key_exists('min', $schema['newSchema'])) {
                        $newColumnImpact->min = $schema['newSchema']['min'];
                    }
                    if (array_key_exists('max', $schema['newSchema'])) {
                        $newColumnImpact->max = $schema['newSchema']['max'];
                    }
                    $newColumnImpact->save();
                }
                else {
                    $newColumnImpact = new ColumnImpact;
                    $newColumnImpact->name = $schema['columnName'];
                    $newColumnImpact->tableImpactId = $tableImpactMem[$schema['tableName']];
                    $newColumnImpact->changeType = 'delete';
                    $newColumnImpact->save();
                }
            }
        }
    }

    public function saveInstanceImpact() {

        foreach($this->dbImpactResult as $changeRequestInputId => $dbImpactList) {
            foreach($dbImpactList['instance'] as $index => $instanceImpactList) {
                if(count($instanceImpactList) > 0) {

                    foreach($instanceImpactList['oldInstance'] as $insIndex => $oldInstance) {
                        $newInstanceImpact = new InstanceImpact;
                        $newInstanceImpact->changeRequestInputId = $changeRequestInputId;
                        $newInstanceImpact->tableName = $dbImpactList['schema'][$index]['tableName'];
                        $newInstanceImpact->columnName = $dbImpactList['schema'][$index]['columnName'];
                        if($dbImpactList['schema'][$index]['changeType'] != 'delete') {
                            $newInstanceImpact->newValue = $instanceImpactList['newInstance'][$insIndex];
                        }
                        $newInstanceImpact->save();
                        //dd($newInstanceImpact->toArray());
                        foreach($oldInstance as $columName => $value) {
                            $oldInstance = new OldInstance;
                            $oldInstance->instanceImpactId = $newInstanceImpact->id;
                            $oldInstance->columnName = $columName;
                            $oldInstance->value = $value;
                            $oldInstance->save();
                        }
                    }
                }
            }
        }
    }
}
