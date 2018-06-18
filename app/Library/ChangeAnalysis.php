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
use App\Model\PkRecord;
use App\Model\RecordImpact;
use App\Model\FrImpact;
use App\Model\FrInputImpact;
use App\Model\TcImpact;
use App\Model\TcInputImpact;
use App\Model\TestCase;
use App\Model\RtmImpact;
use App\Model\CompositeCandidateKeyColumn;
use App\Model\CompositeCandidateKeyImpact;
use App\Model\ForeignKeyColumn;
use App\Model\ForeignKeyImpact;


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
            } else {
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

    public function getSchemaImpactResult()
    {
        return $this->schemaImpactResult;
    }
    public function getInstanceImpactResult()
    {
        return $this->instanceImpactResult;
    }
    public function getKeyConstraintImpactResult()
    {
        return $this->keyConstraintImpactResult;
    }

    public function addKeyConstaintImpactResult(string $tableName, string $consName, string $consType, array $consColumns) : void
    {
        if (!array_key_exists($tableName, $this->keyConstraintImpactResult)) {
            $this->keyConstraintImpactResult[$tableName] = [];
        }

        if (!array_key_exists($consName, $this->keyConstraintImpactResult[$tableName])) {
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
                            'oldValue' => $oldValues ? $oldValues[$index] : null,
                            'newValue' => $newValues ? $newValues[$index] : null,
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
                            'oldValue' => $oldValues ? $oldValues[$index] : null,
                            'newValue' => $newValues ? $newValues[$index] : null,
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
                                    'oldValue' => $oldValues ? $oldValues[$index] : null,
                                    'newValue' => $newValues ? $newValues[$index] : null,
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
        foreach ($this->schemaImpactResult as $tableName => $columnList) {
            foreach ($columnList as $columnName => $info) {
                if ($info['changeType'] == 'delete' || $info['changeType'] == 'edit') {
                    $impact = new ColumnImpact;
                    $impact->crId = $this->changeRequest->id;
                    $impact->changeType = $info['changeType'];
                    $impact->tableName = $tableName;
                    $impact->name = $columnName;
                    $impact->versionType = 'old';
                    $impact->dataType = $info['old']['dataType'];
                    $impact->length = $info['old']['length'];
                    $impact->precision = $info['old']['precision'];
                    $impact->scale = $info['old']['scale'];
                    $impact->default = $info['old']['default'];
                    $impact->nullable = $info['old']['nullable'] ? 'Y' : 'N';
                    $impact->unique = $info['old']['unique'] ? 'Y' : 'N';
                    $impact->min = $info['old']['min'];
                    $impact->max = $info['old']['max'];
                    $impact->save();
                }
                if ($info['changeType'] == 'add' || $info['changeType'] == 'edit') {
                    $impact = new ColumnImpact;
                    $impact->crId = $this->changeRequest->id;
                    $impact->changeType = $info['changeType'];
                    $impact->tableName = $tableName;
                    $impact->name = $columnName;
                    $impact->versionType = 'new';
                    $impact->dataType = isset($info['new']['dataType']) ? $info['new']['dataType'] : null;
                    $impact->length = isset($info['new']['length']) ? $info['new']['length'] : null;
                    $impact->precision = isset($info['new']['precision']) ? $info['new']['precision'] : null;
                    $impact->scale = isset($info['new']['scale']) ? $info['new']['scale'] : null;
                    $impact->default = isset($info['new']['default']) ? $info['new']['default'] : null;
                    $impact->nullable = isset($info['new']['nullable']) ? $info['new']['nullable'] : null;
                    $impact->unique = isset($info['new']['unique']) ? $info['new']['unique'] : null;
                    $impact->min = isset($info['new']['min']) ? $info['new']['min'] : null;
                    $impact->max = isset($info['new']['max']) ? $info['new']['max'] : null;
                    $impact->save();
                }
            }
        }
    }

    public function saveInstanceImpact()
    {
       foreach($this->instanceImpactResult as $tableName => $recordList) {
           foreach($recordList as $row) {
               $recImpact = new RecordImpact;
               $recImpact->crId = $this->changeRequest->id;
               $recImpact->tableName = $tableName;
               $recImpact->save();
               foreach($row['pkRecord'] as $columnName => $value) {
                   $pkRecord = new PkRecord;
                   $pkRecord->recImpactId = $recImpact->id;
                   $pkRecord->columnName = $columnName;
                   $pkRecord->value = $value;
                   $pkRecord->save();
               }
               foreach($row['columnList'] as $columnName => $info) {
                   $instImpact = new InstanceImpact;
                   $instImpact->columnName = $columnName;
                   $instImpact->newValue = $info['newValue'];
                   $instImpact->oldValue = $info['oldValue'];
                   $instImpact->recImpactId = $recImpact->id;
                   $instImpact->save();
               }
           }
       }
    }

    public function saveFrImpact()
    {
        foreach($this->frImpactResult as $no => $frInputList) {
            foreach($frInputList as $name => $info) {
                $frInputImpact = new FrInputImpact;
                $frInputImpact->crId = $this->changeRequest->id;
                $frInputImpact->frNo = $no;
                $frInputImpact->name = $name;
                $frInputImpact->changeType = $info['changeType'];
                $frInputImpact->tableName = $info['tableName'];
                $frInputImpact->columnName = $info['columnName'];
                $frInputImpact->save();
            }
        }
    }

    public function saveTcImpact()
    {
        foreach($this->tcImpactResult as $no => $info) {
            $tcImpact = new TcImpact;
            $tcImpact->crId = $this->changeRequest->id;
            $tcImpact->no = $no;
            $tcImpact->changeType = $info['changeType'];
            $tcImpact->save();
            foreach($info['tcInputList'] as $name => $inputInfo) {
                $tcInputImpact = new TcInputImpact;
                $tcInputImpact->tcImpactId = $tcImpact->save();
                $tcInputImpact->name = $name;
                $tcInputImpact->testDataOld = $inputInfo['old'];
                $tcInputImpact->testDataNew = $inputInfo['new'];
                $tcInputImpact->save();
            }
        }
    }

    public function saveRtmRelationImpact()
    {
        foreach($this->rtmImpactResult as $frNo => $tcList) {
            foreach($tcList as $tcNo => $changeType) {
                $rtmImpact = new RtmImpact;
                $rtmImpact->crId = $this->changeRequest->id;
                $rtmImpact->frNo = $frNo;
                $rtmImpact->tcNo = $tcNo;
                $rtmImpact->changeType = $changeType;
                $rtmImpact->save();
            }
        }
    }

    public function saveKeyConstraintImpact()
    {
        foreach ($this->keyConstraintImpactResult as $tableName => $keyList) {
            foreach ($keyList as $consName => $conInfo) {
                if($conInfo['type'] == 'UNIQUE') {
                    $cckImpact = new CompositeCandidateKeyImpact;
                    $ckkImpact->crId = $this->changeRequest->id;
                    $cckImpact->cckTable = $tableName;
                    $cckImpact->cckName = $consName;
                    $cckImpact->save();
                    foreach($conInfo['columns'] as $columnName) {
                        $cckColumn = new CompositeCandidateKeyColumn;
                        $cckColumn->cckImpactId = $cckImpact->id;
                        $cckColumn->columnName = $columnName;
                        $cckColumn->save();
                    }
                }
                else {
                    $fkImpact = new ForeignKeyImpact;
                    $fkImpact->crId = $this->changeRequest->id;
                    $fkImpact->fkName = $consName;
                    $fkImpact->fkTableName = $tableName;
                    $fkImpact->save();
                    foreach($conInfo['columns'] as $link) {
                        $fkColumn = new ForeignKeyColumn;
                        $fkColumn->fkImpactId = $fkImpact->id;
                        $fkColumn->referencingColumnName = $link['from']['columnName'];
                        $fkColumn->referencedTable = $link['to']['tableName'];
                        $fkColumn->referencedColumnName = $link['to']['columnName'];
                        $fkColumn->save();
                    }
                }
            }
        }
    }
}
