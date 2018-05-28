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
    
    public function __construct(Database $database, ChangeRequestInput $changeRequestInput, DBTargetInterface $dbTargetConnection)
    {
        $this->database = $database;
        $this->changeRequestInput = $changeRequestInput;
        $this->dbTargetConnection = $dbTargetConnection;
        $this->functionalRequirementInput = $this->findFunctionalRequirementInputById($changeRequestInput->functionalRequirementInputId);
    }

    public function analyze(): bool {

        $table = $this->database->getTableByName($this->functionalRequirementInput->tableName);
        $column = $table->getColumnByName($this->functionalRequirementInput->columnName);

        $changeRequest = ChangeRequest::select('projectId')->where('id',$this->changeRequestInput->changeRequestId)->first();
        $functionalRequirements = FunctionalRequirement::select('id')->where('projectId', $changeRequest->projectId)->get();

        $foundOther = false;
        foreach ($functionalRequirements as $fr) {
            if($this->functionalRequirementInput->functionalRequirementId != $fr->id) {
                $frInputs = FunctionalRequirementInput::where([
                ['functionalRequirementId',$fr->id],
                ['tableName',$table->getName()],
                ['columnName',$column->getName()],
                ['activeFlag','Y']
                ])->get();

                if(count($frInputs) > 0) {
                    $foundOther = true;
                    break;
                }
            }
        }

        if($foundOther) { return false; }

        if($this->database->isLinked($table->getName(), $column->getName()) ) {
            if($table->isPK($column->getName())){
                return false;
            }
        
        }

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

            $this->schemaImpactResult[0] = 
            [
                'tableName' => $table->getName(),
                'columnName' => $column->getName(),
                'changeType' => 'delete',
                'oldSchema' => $refSchema,
                'newSchema' => null
            ];
            $this->instanceImpactResult[0] = [
                'oldInstance' => $this->dbTargetConnection->getInstanceByTableName($table->getName()),
                'newInstance' => null
            ];
        
        return true;
    }

    public function modify(): bool {
        //$dbTargetConnection->addColumn($changeRequestInput);
        if($this->schemaImpactResult) {
            $this->dbTargetConnection->disableConstraint();

            $table = $this->database->getTableByName($this->schemaImpactResult[0]['tableName']);
            if($table->isFK($this->schemaImpactResult[0]['columnName'])) {
                $fkName = $table->getFKByColumnName($this->schemaImpactResult[0]['columnName']);
                $dbTargetConnection->dropConstraint($this->schemaImpactResult[0]['tableName'], $fkName->getName());
            }
            
            $relatedUniques = $this->findUniqueConstraintRelated($this->schemaImpactResult[0]['tableName'],$this->schemaImpactResult[0]['columnName']);
            foreach ($relatedUniques as $unique) {
                $this->dbTargetConnection->dropConstraint($this->schemaImpactResult[0]['tableName'],$unique->getName());
            }

            $relatedChecks = $this->findCheckConstraintRelated($this->schemaImpactResult[0]['tableName'],$this->schemaImpactResult[0]['columnName']);
            foreach ($relatedChecks as $check) {
                $this->dbTargetConnection->dropConstraint($this->schemaImpactResult[0]['tableName'],$check->getName());
            }

            $this->dbTargetConnection->dropColumn($this->schemaImpactResult[0]['tableName'],$this->schemaImpactResult[0]['columnName']);

            $this->dbTargetConnection->enableConstraint();
            
        }
        return true;
    }

}