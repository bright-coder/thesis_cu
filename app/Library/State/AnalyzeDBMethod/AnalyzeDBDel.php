<?php 

namespace App\Library\State\AnalyzeDBMethod;

use App\Library\State\AnalyzeDBMethod\AbstractAnalyzeDBMethod;
use App\Model\ChangeRequestInput;
use App\Model\ChangeRequest;
use App\Model\Project;
use App\Model\FunctionalRequirementInput;
use App\Model\FunctionalRequirement;

use App\Library\Database\Database;
use App\Library\CustomModel\DBTargetInterface;

class AnalyzeDBDel extends AbstractAnalyzeDBMethod {

    private $keyConstraintImpact = [];
    
    public function __construct(Database $database, ChangeRequestInput $changeRequestInput, DBTargetInterface $dbTargetConnection)
    {
        $this->database = $database;
        $this->changeRequestInput = $changeRequestInput;
        $this->dbTargetConnection = $dbTargetConnection;
        $this->functionalRequirementInput = $this->findFunctionalRequirementInputById($changeRequestInput->frInputId);
    }

    public function analyze(): array {

        $table = $this->database->getTableByName($this->functionalRequirementInput->tableName);
        $column = $table->getColumnByName($this->functionalRequirementInput->columnName);
        $tableName = $table->getName();
        $columnName = $column->getName();

        $changeRequest = ChangeRequest::select('projectId')->where('id', $this->changeRequestInput->crId)->first();
        $functionalRequirements = FunctionalRequirement::select('id')->where('projectId', $changeRequest->projectId)->get();
        
        $foundOther = false;
        foreach ($functionalRequirements as $fr) {
            if($this->functionalRequirementInput->frId != $fr->id) {
                $frInputs = FunctionalRequirementInput::where([
                ['frId',$fr->id],
                ['tableName',$table->getName()],
                ['columnName',$column->getName()],
                ])->get();

                if(count($frInputs) > 0) {
                    $foundOther = true;
                    break;
                }
            }
        }

        if($foundOther) { return []; }

        $result = [];
        $cckDelete = [];
        $fkDelete = [];
        $refSchema = [
            'dataType' => $column->getDataType()->getType(),
            'length' => $column->getDataType()->getLength(),
            'precision' => $column->getDataType()->getPrecision(),
            'scale' => $column->getDataType()->getScale(),
            'default' => $column->getDefault(),
            'nullable' => $column->isNullable(),
            'unique' => $table->isUnique($column->getName()),
            'min' => $table->getMin($column->getName())['value'],
            'max' => $table->getMax($column->getName())['value']
        ];

        $records = $this->dbTargetConnection->getInstanceByTableName(
            $tableName, 
            array_merge(
                $table->getPK()->getColumns(),
                [$this->functionalRequirementInput->columnName]
            )
        );
        $oldValues = [];
        foreach($records as $index => $record) {
            $oldValues[] = $record[$this->functionalRequirementInput->columnName];
            unset($records[$index][$this->functionalRequirementInput->columnName]);
        }
            $result[$tableName] = [];
            if($table->isFK($column->getName())) {
                foreach($this->findFKRelated($tableName, $columnName) as $fk) {
                    $fkDelete[] = [
                        'tableName' => $tableName,
                        'info' => $fk
                    ];
                }
                
            }
            if($table->isUnique($column->getName())) {
                foreach($this->findUniqueConstraintRelated($tableName, $columnName) as $unique) {
                    if(count($unique->getColumns()) > 1) {
                        $cckDelete[] = [
                            'tableName' => $tableName,
                            'info' => $unique
                        ];
                    }
                }
            }

                $result[$tableName][$columName] = [
                    'changeType' => 'add',
                    'old' => [],
                    'new' => $newSchema,
                    'isPK' => false,
                    'instance' => [
                        'pkRecords' => $records,
                        'oldValues' => $oldValues,
                        'newValues' => []
                    ]
                ];

                $result = [
                    'tableList' => $result,
                    'cckDelete' => $cckDelete,
                    'fkDelete' => $fkDelete
                ];
                
        
        return $result;
    }

    public function modify(): bool {
        //$dbTargetConnection->addColumn($changeRequestInput);
        if(count($this->schemaImpactResult) > 0) {
            $this->dbTargetConnection->disableConstraint();
            $tableImpact = array_slice($this->schemaImpactResult,0,1);
            $columnImpact = array_slice($tableImpact['columnList'],0,1);
            $table = $this->database->getTableByName($tableImpact['tableName']);
            if($table->isFK($columnImpact['columnName'])) {
                $fkName = $table->getFKByColumnName($columnImpact['columnName']);
                $dbTargetConnection->dropConstraint($tableImpact['tableName'], $fkName->getName());
                
            }
            
            $relatedUniques = $this->findUniqueConstraintRelated($tableImpact['tableName'],$columnImpact['columnName']);
            foreach ($relatedUniques as $unique) {
                $this->dbTargetConnection->dropConstraint($tableImpact['tableName'],$unique->getName());
            }

            $relatedChecks = $this->findCheckConstraintRelated($tableImpact['tableName'],$columnImpact['columnName']);
            foreach ($relatedChecks as $check) {
                $this->dbTargetConnection->dropConstraint($tableImpact['tableName'],$check->getName());
            }

            $this->dbTargetConnection->dropColumn($tableImpact['tableName'],$columnImpact['columnName']);

            $this->dbTargetConnection->enableConstraint();
            
        }
        return true;
    }

}