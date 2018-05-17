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
use App\Model\FrImpact;
use App\Model\FrInputImpact;
use App\Model\TcImpact;
use App\Model\TcInputImpact;
use App\Model\TestCase;
use App\Model\RtmRelationImpact;

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
                if (!array_key_exists($schema['tableName'], $tableImpactMem)) {
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
                if(!empty($instanceImpactList)) {

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

    public function saveFrImpact() {
        $changeRequestId = $this->getChangeRequest()->id;
        foreach($this->frImpactResult as $frImpact) {
            $newFrImpact = new FrImpact;
            $newFrImpact->changeRequestId = $changeRequestId;
            $newFrImpact->no = $frImpact['no'];
            $newFrImpact->save();
            foreach($frImpact['inputList'] as $input) {
                if(!empty($input['new'])) {
                    $newFrInputImpact = new FrInputImpact;
                    $newFrInputImpact->frInputImpactId = $newFrImpact->id;
                    $newFrInputImpact->changeType = $input['changeType'];
                    $newFrInputImpact->versionType = 'new';
                    if (array_key_exists('dataType', $input['new'])) {
                        $newFrInputImpact->dataType = $input['new']['dataType'];
                    }
                    if (array_key_exists('length', $input['new'])) {
                        $newFrInputImpact->length = $input['new']['length'];
                    }
                    if (array_key_exists('precision', $input['new'])) {
                        $newFrInputImpact->precision = $input['new']['precision'];
                    }
                    if (array_key_exists('scale', $input['new'])) {
                        $newFrInputImpact->scale = $input['new']['scale'];
                    }
                    if (array_key_exists('default', $input['new'])) {
                        $newFrInputImpact->default = $input['new']['default'];
                    }
                    if (array_key_exists('nullable', $input['new'])) {
                        $newFrInputImpact->nullable = $input['new']['nullable'];
                    }
                    if (array_key_exists('unique', $input['new'])) {
                        $newFrInputImpact->unique = $input['new']['unique'];
                    }
                    if (array_key_exists('min', $input['new'])) {
                        $newFrInputImpact->min = $input['new']['min'];
                    }
                    if (array_key_exists('max', $input['new'])) {
                        $newFrInputImpact->max = $input['new']['max'];
                    }
                    if (array_key_exists('tableName', $input['new'])) {
                        $newFrInputImpact->tableName = $input['new']['tableName'];
                    }
                    if (array_key_exists('columnName', $input['new'])) {
                        $newFrInputImpact->tableName = $input['new']['columnName'];
                    }
                    $newFrInputImpact->save();
                }
                if(!empty($input['old'])) {
                    $newFrInputImpact = new FrInputImpact;
                    $newFrInputImpact->frInputImpactId = $newFrImpact->id;
                    $newFrInputImpact->changeType = $input['changeType'];
                    $newFrInputImpact->versionType = 'old';
                    $newColumnImpact->length = $input['old']['length'];
                    $newColumnImpact->precision = $input['old']['precision'];
                    $newColumnImpact->scale = $input['old']['scale'];
                    $newColumnImpact->default = $input['old']['default'];
                    $newColumnImpact->nullable = $input['old']['nullable'];
                    $newColumnImpact->unique = $input['old']['unique'];
                    $newColumnImpact->min = $input['old']['min'];
                    $newColumnImpact->max = $input['old']['max'];
                    $newColumnImpact->tableName = $input['old']['tableName'];
                    $newColumnImpact->columnName = $input['old']['columnName'];
                    $newColumnImpact->save();
                }
            }
        }
    }

    public function saveTcImpact() {
        $changeRequestId = $this->getChangeRequest()->id;
        foreach($this->tcImpactResult as $tcImpact) {
            $newTcImpact = new TcImpact;
            $newTcImpact->changeRequestId = $changeRequestId;
            $newTcImpact->no = $tcImpact['changeType'] == 'add' ? $tcImpact['newNo'] : TestCase::find($tcImpact['oldTcId'])->no;
            $newTcImpact->changeType = $tcImpact['changeType'];
            $newTcImpact->save();
            
            if($changeType == 'edit' && !empty($tcImpact['tcInputEdit'])) {
                foreach ($tcImpact['tcInputEdit'] as $tcInputEdit) {
                    $newTcInputEdit = new TcInputImpact;
                    $newTcInputEdit->tcImpactId = $newTcImpact->id;
                    $newTcInputEdit->inputName = $tcInputEdit['inputName'];
                    $newTcInputEdit->testDataOld = $tcInputEdit['old'];
                    $newTcInputEdit->testDataNew = $tcInputEdit['new'];
                    $newTcInputEdit->save();
                }
            }
        }
    }

    public function saveRtmRelationImpact() {
        $changeRequestId = $this->getChangeRequest()->id;
        foreach($this->rtmImpactResult as $rtmImpact) {
            $newRtmImpact = new RtmRelationImpact;
            $newRtmImpact->changeRequestId = $changeRequestId;
            $newRtmImpact->functionalRequirementNo = $rtmImpact['functionalRequirementNo'];
            $newRtmImpact->testCaseNo = $rtmImpact['testCaseNo'];
            $newRtmImpact->changeType = $rtmImpact['changeType'];
            $newRtmImpact->save();
        }
    }
}
