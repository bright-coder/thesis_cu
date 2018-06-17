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

    private $schemaImpactResult = [];
    private $instanceImpactResult = [];
    private $keyConstraintImpactResult = [];
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

    public function getChangeFrId(): string
    {
        return $this->changeRequest->changeFrId;
    }

    public function addSchemaImpactResult(string $tableName, string $columName, string $changeType, array $oldCol, array $newCol, bool $isPK) : void
    {
        if (!array_key_exists($tableName, $this->schemaImpactResult)) {
            $this->schemaImpactResult[$tableName] = [];
        }
        if (!array_key_exists($columName, $this->schemaImpactResult[$tableName])) {
            $this->schemaImpactResult[$tableName][$columName] = [];
        }

        if (!empty($this->schemaImpactResult[$tableName][$columName])) {
            if ($this->schemaImpactResult[$tableName][$columName]['changeType'] == 'edit') {
                //composite foreign key
                $this->schemaImpactResult[$tableName][$columName]['new']['unique'] = 'N';
            }
            else {
                $this->schemaImpactResult[$tableName][$columName] = [
                    'changeType' => $changeType,
                    'old' => $oldCol,
                    'new' => $newCol,
                    'isPK' => $isPK
                ];
            }
        } else {
            $this->schemaImpactResult[$tableName][$columName] = [
                'changeType' => $changeType,
                'old' => $oldCol,
                'new' => $newCol,
                'isPK' => $isPK
            ];
        }
    }

    public function getSchemaImpactResult() { return $this->schemaImpactResult; }
    public function getInstanceImpactResult() { return $this->instanceImpactResult; }
    public function getKeyConstraintImpactResult() { return $this->keyConstraintImpactResult; }

    public function addKeyConstaintImpactResult(string $tableName, string $consName, string $consType, array $consColumns) : void {
        if (!array_key_exists($tableName, $this->keyConstraintImpactResult)) {
            $this->keyConstraintImpactResult[$tableName] = [];
        }

        if(!array_key_exists($consName, $this->keyConstraintImpactResult[$tableName])) {
            $this->keyConstraintImpactResult[$tableName][$consName] = [
                'type' => $consType,
                'columns' => $consColumns
            ];
        }
    }


    public function addInstanceResult(string $tableName, string $columName, array $pkRecords, string $changeType, array $oldValues = [], array $newValues = []): void
    {
        if (!array_key_exists($tableName, $this->instanceImpactResult)) {
            $this->instanceImpactResult[$tableName] = [];
        }
        // array of record
        if (!$this->instanceImpactResult[$tableName]) {
            foreach ($pkRecords as $index => $pkRecord) {
                $this->instanceImpactResult[$tableName][] = [
                    'pkRecord' => $pkRecord,
                    'columnList' => [
                        $columName => [
                            'oldValue' => $oldValues ? $oldValues[$index] : [],
                            'newValue' => $newValues ? $newValues[$index] : [],
                            'changeType' => $changeType
                        ]
                    ]
                ];
            }
        } else {
            $memo = [];
            foreach ($pkRecords as $index => $pkRecord) {
                foreach ($this->instanceImpactResult[$tableName] as $indexInstance => $instance) {
                    if ($pkRecord == $instance['pkRecord']) {
                        $this->instanceImpactResult[$tableName][$indexInstance]['columnList'][$columName] = [
                            'oldValue' => $oldValues ? $oldValues[$index] : [],
                            'newValue' => $newValues ? $newValues[$index] : [],
                            'changeType' => $changeType
                        ];
                        $memo[$index] = true;
                    }
                }
            }
            if (count($memo) != count($pkRecords)) {
                foreach ($pkRecords as $index => $pkRecord) {
                    if (!\array_key_exists($index, $memo)) {
                        $this->instanceImpactResult[$tableName][] = [
                            'pkRecord' => $pkRecord,
                            'columnList' => [
                                $columName => [
                                    'oldValue' => $oldValues ? $oldValues[$index] : [],
                                    'newValue' => $newValues ? $newValues[$index] : [],
                                    'changeType' => $changeType
                                ]
                            ]
                        ];
                    }
                }
            }
        }
    }

    public function addTcImpactResult(array $tcImpactResult): void
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

    public function addFRImpactResult(array $frImpactResult) : void
    {
        $this->frImpactResult = $frImpactResult;
    }

    public function getFrImpactResult(): array
    {
        return $this->frImpactResult;
    }

    public function addRtmImpactResult(array $rtmImpactResult): void
    {
        $this->rtmImpactResult = $rtmImpactResult;
    }

    public function getRtmImpactResult(): array
    {
        return $this->rtmImpactResult;
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
        
          
    }

    public function saveInstanceImpact()
    {
        foreach ($this->dbImpactResult as $changeRequestInputId => $dbImpactList) {
            foreach ($dbImpactList['instance'] as $index => $instanceImpactList) {
                if (!empty($instanceImpactList)) {
                    foreach ($instanceImpactList['oldInstance'] as $insIndex => $oldInstance) {
                        $newInstanceImpact = new InstanceImpact;
                        $newInstanceImpact->changeRequestInputId = $changeRequestInputId;
                        $newInstanceImpact->tableName = $dbImpactList['schema'][$index]['tableName'];
                        $newInstanceImpact->columnName = $dbImpactList['schema'][$index]['columnName'];
                        if ($dbImpactList['schema'][$index]['changeType'] != 'delete') {
                            $newInstanceImpact->newValue = $instanceImpactList['newInstance'][$insIndex];
                        }
                        $newInstanceImpact->save();
                        //dd($newInstanceImpact->toArray());
                        foreach ($oldInstance as $columName => $value) {
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

    public function saveFrImpact()
    {
        $changeRequestId = $this->getChangeRequest()->id;
        foreach ($this->frImpactResult as $frImpact) {
            $newFrImpact = new FrImpact;
            $newFrImpact->changeRequestId = $changeRequestId;
            $newFrImpact->no = $frImpact['no'];
            $newFrImpact->save();
            foreach ($frImpact['inputList'] as $input) {
                if (!empty($input['new'])) {
                    $newFrInputImpact = new FrInputImpact;
                    $newFrInputImpact->frImpactId = $newFrImpact->id;
                    $newFrInputImpact->changeType = $input['changeType'];
                    $newFrInputImpact->versionType = 'new';
                    if (array_key_exists('name', $input['new'])) {
                        $newFrInputImpact->name = $input['new']['name'];
                    }
                    if ($newFrInputImpact->changeType == 'edit') {
                        $newFrInputImpact->name = $input['old']['name'];
                    }
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
                        $newFrInputImpact->columnName = $input['new']['columnName'];
                    }
                    $newFrInputImpact->save();
                }
                if (!empty($input['old'])) {
                    $newFrInputImpact = new FrInputImpact;
                    $newFrInputImpact->frImpactId = $newFrImpact->id;
                    $newFrInputImpact->changeType = $input['changeType'];
                    $newFrInputImpact->versionType = 'old';
                    $newFrInputImpact->name = $input['old']['name'];
                    $newFrInputImpact->dataType = $input['old']['dataType'];
                    $newFrInputImpact->length = $input['old']['length'];
                    $newFrInputImpact->precision = $input['old']['precision'];
                    $newFrInputImpact->scale = $input['old']['scale'];
                    $newFrInputImpact->default = $input['old']['default'];
                    $newFrInputImpact->nullable = $input['old']['nullable'];
                    $newFrInputImpact->unique = $input['old']['unique'];
                    $newFrInputImpact->min = $input['old']['min'];
                    $newFrInputImpact->max = $input['old']['max'];
                    $newFrInputImpact->tableName = $input['old']['tableName'];
                    $newFrInputImpact->columnName = $input['old']['columnName'];
                    $newFrInputImpact->save();
                }
            }
        }
    }

    public function saveTcImpact()
    {
        $changeRequestId = $this->getChangeRequest()->id;
        foreach ($this->tcImpactResult as $tcImpact) {
            $newTcImpact = new TcImpact;
            $newTcImpact->changeRequestId = $changeRequestId;
            $newTcImpact->no = $tcImpact['changeType'] == 'add' ? $tcImpact['newNo'] : TestCase::find($tcImpact['oldTcId'])->no;
            $newTcImpact->changeType = $tcImpact['changeType'];
            $newTcImpact->save();
            
            if ($tcImpact['changeType'] == 'edit' && !empty($tcImpact['tcInputEdit'])) {
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

    public function saveRtmRelationImpact()
    {
        $changeRequestId = $this->getChangeRequest()->id;
        foreach ($this->rtmImpactResult as $rtmImpact) {
            $newRtmImpact = new RtmRelationImpact;
            $newRtmImpact->changeRequestId = $changeRequestId;
            $newRtmImpact->functionalRequirementNo = $rtmImpact['functionalRequirementNo'];
            $newRtmImpact->testCaseNo = $rtmImpact['testCaseNo'];
            $newRtmImpact->changeType = $rtmImpact['changeType'];
            $newRtmImpact->save();
        }
    }
}
