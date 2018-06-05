<?php

namespace App\Library;

use App\Model\ChangeRequest;
use App\Model\ChangeRequestInput;
use App\Model\ColumnImpact;
use App\Model\OldInstance;
use App\Model\InstanceImpact;
use App\Model\Project;
use App\Model\FrImpact;
use App\Model\FrInputImpact;
use App\Model\FunctionalRequirement;
use App\Model\FunctionalRequirementInput;
use App\Model\TestCase;
use App\Model\TestCaseInput;
use App\Model\TcImpact;
use App\Model\TcInputImpact;
use App\Model\RequirementTraceabilityMatrix;
use App\Model\RequirementTraceabilityMatrixRelation;
use App\Model\RtmRelationImpact;
use App\Library\Database\Database;
use App\Library\Builder\DatabaseBuilder;
use App\Library\CustomModel\DBTargetConnection;
use App\Library\CustomModel\DBTargetInterface;

class CancelChangeRequest
{
    private $cancelCrId;
    private $projectId;
    /**
     * Undocumented variable
     *
     * @var Database
     */
    private $dbTarget;

    /**
     * Undocumented variable
     *
     * @var DBTargetInterface
     */
    private $dbTargetConnection = null;

    private $fkTrace = [];

    public function __construct(int $projectId, int $cancelCrId)
    {
        $this->projectId = $projectId;
        $this->cancelCrId = $cancelCrId;
    }

    private function connectTargetDB(string $projectId): bool
    {
        $project = Project::where('id', $projectId)->first();
        $this->dbTargetConnection = DBTargetConnection::getInstance(
            $project->dbType,
            $project->dbServer,
            $project->dbPort,
            $project->dbName,
            $project->dbUsername,
            $project->dbPassword
        );

        if (!$this->dbTargetConnection->connect()) {
            return false;
        }

        return true;
    }

    private function getDbSchema(): void
    {
        $databaseBuilder = new DatabaseBuilder($this->dbTargetConnection);
        $databaseBuilder->setUpTablesAndColumns();
        $this->dbTarget = $databaseBuilder->getDatabase();
    }
    
    public function cancel(): bool
    {
        if (!$this->connectTargetDB($this->projectId)) {
            return false;
        }

        $changeRequestList = ChangeRequest::where([
            ['id', '>=', $this->cancelCrId],
            ['projectId', $this->projectId],
            //['cancelStatus', 0]
        ])->orderBy('id', 'desc')->get();

        foreach ($changeRequestList as $changeRequest) {
            if ($this->isStatusSuccess($changeRequest->id)) {
                //$impactResult = (new ImpactResult($changeRequest->id))->getImpact();
                $this->reverseDatabase($changeRequest->id);
                // # More Code
                $this->reverseRTM($changeRequest->id);
                $this->reverseTestCase($changeRequest->id);
                $this->reverseFunctionalRequirement($changeRequest->id);
                //$this->reverseFunctionalRequirement($changeRequest->id);
                //$this->reverseTestCase($changeRequest->id);
                //$this->reverseRTM($changeRequest->id);
            }
            
        }

        return true;
    }

    private function isStatusSuccess(int $changeRequestId): bool
    {
        $changeRequestInputList = ChangeRequestInput::where('changeRequestId', $changeRequestId)->get();
        foreach ($changeRequestInputList as $crInput) {
            if ($crInput->status == 0) {
                return false;
            }
        }
        return true;
    }

    private function reverseDatabase(int $changeRequestId): void
    {
        $crInputList = ChangeRequestInput::select('id')->where('changeRequestId', $changeRequestId)->get();
        $crInputId = [];
        foreach ($crInputList as $crInput) {
            $crInputId[] = $crInput->id;
        }
        $columnImpactList = ColumnImpact::whereIn('changeRequestInputId', $crInputId)->orderBy('changeRequestInputId', 'desc')->get();
        $crInputGroup = [];
        foreach ($columnImpactList as $columnImpact) {
            if (!array_key_exists($columnImpact->changeRequestInputId, $crInputGroup)) {
                $crInputGroup[$columnImpact->changeRequestInputId] = [];
            }
            if (!array_key_exists($columnImpact->tableName, $crInputGroup[$columnImpact->changeRequestInputId])) {
                $crInputGroup[$columnImpact->changeRequestInputId][$columnImpact->tableName] = [];
            }
            if (!array_key_exists($columnImpact->name, $crInputGroup[$columnImpact->changeRequestInputId][$columnImpact->tableName])) {
                $crInputGroup[$columnImpact->changeRequestInputId][$columnImpact->tableName][$columnImpact->name] = [];
            }
            $crInputGroup[$columnImpact->changeRequestInputId][$columnImpact->tableName][$columnImpact->name][$columnImpact->versionType] = $columnImpact;
        }
        $this->fkTrace = [];
        $this->dbTargetConnection->disableConstraint();
        foreach ($crInputGroup as $crInputId => $tableList) {
            foreach ($tableList as $tableName => $columnList) {
                foreach ($columnList as $columnName  => $column) {
                    if (array_key_exists('old', $column)) {
                        $changeType = $column['old']->changeType;
                    } else {
                        $changeType = $column['new']->changeType;
                    }
                    
                    if ($changeType == 'add') {
                        $this->deleteColumn($column['new']);
                    } elseif ($changeType == 'edit') {
                        $this->editColumn($column['old']);
                    } elseif ($changeType == 'delete') {
                        $this->addColumn($column['old']);
                    }
                }
            }
        }
        foreach ($this->fkTrace as $fkRelated) {
            $this->dbTargetConnection->addForeignKeyConstraint($fkRelated['tableName'], $fkRelated['fk']->getColumns(), $fkRelated['fk']->getName());
        }
        $this->dbTargetConnection->enableConstraint();
    }

    private function editColumn(ColumnImpact $column) : void
    {
        $this->getDbSchema();
        $columnArray = $column->toArray();
        $columnArray['columnName'] = $column->name."_#temp";
        unset($columnArray['name']);

        $this->dbTargetConnection->disableConstraint();

        $this->dbTargetConnection->addColumn($columnArray);

        $instanceImpactList = InstanceImpact::where([
            ['changeRequestInputId', $column->changeRequestInputId],
            ['tableName', $column->tableName],
            ['columnName', 1]
        ])->get();
        
        if (!count($instanceImpactList) > 0) {
            $instanceImpactList = InstanceImpact::where([
                ['changeRequestInputId', $column->changeRequestInputId],
                ['tableName', $column->tableName],
                ['columnName', $column->name]
            ])->get();
            
            $newInstance = [];
            $oldInstance = [];
            foreach ($instanceImpactList as $instanceImpact) {
                $oldRecordList = OldInstance::where('instanceImpactId', $instanceImpact->id)->get();
                $record = [];
                foreach ($oldRecordList as $oldRecord) {
                    $record[$oldRecord->columnName] = $oldRecord->value;
                }
                $newInstance[] = $record[$column->name];
                unset($record[$column->name]);
                $oldInstance[] = $record;
            }

            if ($column->default == '#NULL') {
                $default = null;
            } else {
                $default = $column->default;
            }
    
            $this->dbTargetConnection->updateInstance(
                $column->tableName,
                $column->name."_#temp",
                $oldInstance,
                $newInstance,
                $default
            );
        } else {
            $oldValues = [];
            foreach ($this->dbTargetConnection->getInstanceByTableName($column->tableName) as $oldRecord) {
                $oldValues[] = $oldRecord[$column->name];
            }
            $this->dbTargetConnection->updateInstance(
                    $column->tableName,
                    $column->name."_#temp",
                    $this->dbTargetConnection->getInstanceByTableName($column->tableName),
                    $oldValues,
                    $column->name
                );
        }

        $uniqueConstraintList = $this->findUniqueConstraintRelated($column->tableName, $column->name);
        if (count($uniqueConstraintList) > 0) {
            foreach ($uniqueConstraintList as $uniqueConstraint) {
                $this->dbTargetConnection->dropConstraint($column->tableName, $uniqueConstraint->getName());
            }
        }
        $checkConstraintList = $this->findCheckConstraintRelated($column->tableName, $column->name);
        if (count($checkConstraintList) > 0) {
            foreach ($checkConstraintList as $checkConstraint) {
                $this->dbTargetConnection->dropConstraint($column->tableName, $checkConstraint->getName());
            }
        }
        
        if ($this->dbTarget->getTableByName($column->tableName)->isPK($column->name)) {
            $fkRelatedList = $this->findFKRelated($column->tableName, $column->name);
            foreach ($fkRelatedList as $fkRelated) {
                $this->dbTargetConnection->dropConstraint($column->tableName, $fkRelated['fk']->getName());
            }
            $this->dbTargetConnection->dropConstraint($column->tableName, $this->dbTarget->getTableByName($column->tableName)->getPK()->getName());
            $this->fkTrace[] = $fkRelatedList;
            $pk = [
                'tableName' => $column->tableName,
                'name' => $this->dbTarget->getTableByName($column->tableName)->getPK()->getName(),
                'columns' => $this->dbTarget->getTableByName($column->tableName)->getPK()->getColumns()
            ];
        }

        $this->dbTargetConnection->dropColumn($column->tableName, $column->name);
        $this->dbTargetConnection->updateColumn($columnArray);
        $this->dbTargetConnection->updateColumnName($column->tableName, $column->name."_#temp", $column->name);

        if ($this->dbTarget->getTableByName($column->tableName)->isPK($column->name)) {
            $this->dbTargetConnection->addPrimaryKeyConstraint(
                $pk['tableName'],
                $pk['columns'],
                $pk['name']
            );
        } else {
            // if is composite move to the same level of fkTrace;
            if (strcmp($column->unique, 'Y') == 0) { //&& $scResult['oldSchema']['unique'] === false
                $this->dbTargetConnection->addUniqueConstraint($column->tableName, $column->name);
            }
        }
    }

    private function addColumn(ColumnImpact $column) : void
    {
        $this->getDbSchema();
        $columnArray = $column->toArray();
        $columnArray['columnName'] = $column->name;
        unset($columnArray['name']);
        $this->dbTargetConnection->addColumn($columnArray);

        $default = $column->default == '#NULL' ? null : $column->default;

        $instanceImpactList = InstanceImpact::where([
            ['changeRequestInputId', $column->changeRequestInputId],
            ['tableName', $column->tableName],
            ['columnName', $column->name]
        ])->get();
        
        $newInstance = [];
        $oldInstance = [];
        foreach ($instanceImpactList as $instanceImpact) {
            $oldRecordList = OldInstance::where('instanceImpactId', $instanceImpact->id)->get();
            $record = [];
            foreach ($oldRecordList as $oldRecord) {
                $record[$oldRecord->columnName] = $oldRecord->value;
            }
            $newInstance[] = $record[$column->name];
            unset($record[$column->name]);
            $oldInstance[] = $record;
        }

        $this->dbTargetConnection->updateInstance(
            $column->tableName,
            $column->name,
            $oldInstance,
            $newInstance,
            $default
        );

        $this->dbTargetConnection->updateColumn($columnArray);
        
        if (strcasecmp($column->unique, 'N') == 0 ? false : true) {
            $this->dbTargetConnection->addUniqueConstraint($column->tableName, $column->name);
        }
        
        if ($column->min !== null || $column->max !== null) {
            switch ($column->dataType) {
                case 'int':
                case 'float':
                case 'decimal':
                    $this->dbTargetConnection->addCheckConstraint(
                        $column->tableName,
                        $column->name,
                        $column->min,
                        $column->max
                    );
                    break;
                default:
                    # code...
                    break;
            }
        }
    }

    private function deleteColumn(ColumnImpact $column) : void
    {
        $this->getDbSchema();

        $table = $this->dbTarget->getTableByName($column->tableName);
        if ($table->isFK($column->name)) {
            $fkName = $table->getFKByColumnName($column->name);
            $dbTargetConnection->dropConstraint($column->tableName, $fkName->getName());
        }
        
        $relatedUniques = $this->findUniqueConstraintRelated($column->tableName, $column->name);
        foreach ($relatedUniques as $unique) {
            $this->dbTargetConnection->dropConstraint($column->tableName, $unique->getName());
        }

        $relatedChecks = $this->findCheckConstraintRelated($column->tableName, $column->name);
        foreach ($relatedChecks as $check) {
            $this->dbTargetConnection->dropConstraint($column->tableName, $check->getName());
        }

        $this->dbTargetConnection->dropColumn($column->tableName, $column->name);
    }

    private function findUniqueConstraintRelated(string $tableName, string $columnName): array
    {
        $uniqueConstraints = $this->dbTarget->getTableByName($tableName)->getAllUniqueConstraint();
        $arrayUniqueRelated = [];
        foreach ($uniqueConstraints as $uniqueConstraint) {
            foreach ($uniqueConstraint->getColumns() as $column) {
                if ($column == $columnName) {
                    $arrayUniqueRelated[] = $uniqueConstraint;
                    break;
                }
            }
        }
        return $arrayUniqueRelated;
    }

    private function findCheckConstraintRelated(string $tableName, string $columnName): array
    {
        $checkConstraints = $this->dbTarget->getTableByName($tableName)->getAllCheckConstraint();
        $arrayCheckRelated = [];
        foreach ($checkConstraints as $checkConstraint) {
            foreach ($checkConstraint->getColumns() as $column) {
                if ($column == $columnName) {
                    $arrayCheckRelated[] = $checkConstraint;
                    break;
                }
            }
        }
        return $arrayCheckRelated;
    }

    private function findFKRelated(string $tableName, string $columnName) : array
    {
        $result = [];
        foreach ($this->dbTarget->getAllTables() as $table) {
            foreach ($table->getAllFK() as $fk) {
                foreach ($fk->getColumns() as $link) {
                    if ($link['to']['tableName'] == $tableName && $link['to']['columnName'] == $columnName) {
                        $result[] = [
                            'tableName' => $link['from']['tableName'],
                            'fk' => $fk
                        ];
                    }
                }
            }
        }
        return $result;
    }

    private function reverseFunctionalRequirement(int $changeRequestId)
    {
        $frImpactList = FrImpact::where('changeRequestId', $changeRequestId)->get();

        foreach ($frImpactList as $frImpact) {
            $frInputImpactList = FrInputImpact::where('frImpactId', $frImpact->id)->get();
            $frInputGroup = [];
            foreach ($frInputImpactList as $frInputImpact) {
                if (!array_key_exists($frInputImpact->name, $frInputGroup)) {
                    $frInputGroup[$frInputImpact->name] = [
                        'changeType' => $frInputImpact->changeType,
                    ];
                }
                $frInputGroup[$frInputImpact->name][$frInputImpact->versionType] = $frInputImpact;
            }
            $projectId = ChangeRequest::where('id', $changeRequestId)->first()->projectId;
            $frId = FunctionalRequirement::where([
                        ['projectId', $projectId],
                        ['no', $frImpact->no]
                    ])->first()->id;
            foreach ($frInputGroup as $frInputName => $frInputImpact) {
                if ($frInputImpact['changeType'] == 'add') {
                    $frInput = FunctionalRequirementInput::where([
                        ['functionalRequirementId', $frId],
                        ['name', $frInputName],
                        ['activeFlag', 'Y']
                    ])->orderBy('id', 'desc')->first();
                    $frInput->activeFlag = 'N';
                    $frInput->save();
                }
                elseif($frInputImpact['changeType'] == 'edit') {
                    $frInputOld = FunctionalRequirementInput::where([
                        ['functionalRequirementId', $frId],
                        ['name', $frInputName],
                        ['activeFlag', 'N']
                    ])->orderBy('id', 'desc')->first();
                    $frInputOld->activeFlag = 'Y';

                    $frInputNew = FunctionalRequirementInput::where([
                        ['functionalRequirementId', $frId],
                        ['name', $frInputName],
                        ['activeFlag', 'Y']
                    ])->orderBy('id', 'desc')->first();
                    $frInputNew->activeFlag = 'N';
                    //$frInput->save();
                    $frInputOld->save();
                    $frInputNew->save();
                }
                else {
                    $frInput = FunctionalRequirementInput::where([
                        ['functionalRequirementId', $frId],
                        ['name', $frInputName],
                        ['activeFlag', 'N']
                    ])->orderBy('id', 'desc')->first();
                    $frInput->activeFlag = 'Y';
                    $frInput->save();
                }
            }
        }
    }

    private function reverseTestCase(int $changeRequestId)
    {
        $tcImpactList = TcImpact::where('changeRequestId', $changeRequestId)->get();
        $projectId = ChangeRequest::where('id', $changeRequestId)->first()->projectId;

        foreach($tcImpactList as $tcImpact) {
            $gg[$tcImpact->no] = [];
            if($tcImpact->changeType == 'add') {
                $testCase = TestCase::where([
                    ['no', $tcImpact->no],
                    ['projectId', $projectId],
                    ['activeFlag', 'Y']
                ])->orderBy('id', 'desc')->first();
                $testCase->activeFlag = 'N';
                $testCase->save();
            }
            elseif($tcImpact->changeType == 'edit') {
                $testCase = TestCase::where([
                    ['no', $tcImpact->no],
                    ['projectId', $projectId],
                    ['activeFlag', 'Y']
                ])->orderBy('id', 'desc')->first();
                $tcInputEditList = TcInputImpact::where('tcImpactId', $tcImpact->id)->get();
                

                foreach($tcInputEditList as $tcInputEdit) {
                    
                    $tcInput = TestCaseInput::where([
                        ['testCaseId', $testCase->id],
                        ['name', $tcInputEdit->inputName],
                        ['testData', $tcInputEdit->testDataNew]
                    ])->orderBy('id', 'desc')->first();
                    $tcInput->testData = trim($tcInputEdit->testDataOld);
                    $tcInput->save();
                }
            }
            else {
                $testCase = TestCase::where([
                    ['no', $tcImpact->no],
                    ['projectId', $projectId],
                    ['activeFlag', 'N']
                ])->orderBy('id', 'desc')->first();
                $testCase->activeFlag = 'Y';
                $testCase->save();
            }
        }

    }

    private function reverseRTM(int $changeRequestId)
    {
        $projectId = ChangeRequest::where('id', $changeRequestId)->first()->projectId;
        $rtmId = RequirementTraceabilityMatrix::where('projectId', $projectId)->first()->id;

        $rtmRelationImpactList = RtmRelationImpact::where('changeRequestId', $changeRequestId)->get();

        foreach($rtmRelationImpactList as $rtmRelationImpact) {
            $frId = FunctionalRequirement::where([
                ['projectId', $projectId],
                ['no', $rtmRelationImpact->functionalRequirementNo],
            ])->first()->id;
            $tcId = TestCase::where([
                ['projectId', $projectId],
                ['no', $rtmRelationImpact->testCaseNo],
                // ['activeFlag', 'Y']
            ])->orderBy('id','desc')->first()->id;
            
            // $rtmRelation = RequirementTraceabilityMatrixRelation::where([
            //     ['requirementTraceabilityMatrixId', $rtmId],
            //     ['functionalRequirementId', $frId],
            //     ['testCaseId', $tcId]
            // ])->orderBy('id','desc')->first();
            if($rtmRelationImpact == 'add') {
                $rtmRelation = RequirementTraceabilityMatrixRelation::where([
                    ['requirementTraceabilityMatrixId', $rtmId],
                    ['functionalRequirementId', $frId],
                    ['testCaseId', $tcId],
                    ['activeFlag', 'Y']
                ])->first();
                $rtmRelation->activeFlag = 'N';
            }
            else {
                $rtmRelation = RequirementTraceabilityMatrixRelation::where([
                    ['requirementTraceabilityMatrixId', $rtmId],
                    ['functionalRequirementId', $frId],
                    ['testCaseId', $tcId],
                    ['activeFlag', 'N']
                ])->orderBy('id','desc')->first();
                $rtmRelation->activeFlag = 'Y';
            }
        }
    }
}
