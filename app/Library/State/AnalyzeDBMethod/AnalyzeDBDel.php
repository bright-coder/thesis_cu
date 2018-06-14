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

class AnalyzeDBDel extends AbstractAnalyzeDBMethod
{
    private $keyConstraintImpact = [];
    
    public function __construct(Database $database, ChangeRequestInput $changeRequestInput, DBTargetInterface $dbTargetConnection)
    {
        $this->database = $database;
        $this->changeRequestInput = $changeRequestInput;
        $this->dbTargetConnection = $dbTargetConnection;
        $this->functionalRequirementInput = $this->getFRInputById($changeRequestInput->frInputId);
    }

    public function analyze(): array
    {
        $table = $this->database->getTableByName($this->functionalRequirementInput->tableName);
        $column = $table->getColumnByName($this->functionalRequirementInput->columnName);
        $tableName = $table->getName();
        $columnName = $column->getName();

        $changeRequest = ChangeRequest::select('projectId')->where('id', $this->changeRequestInput->crId)->first();
        $functionalRequirements = FunctionalRequirement::select('id')->where('projectId', $changeRequest->projectId)->get();
        
        $foundOther = false;
        foreach ($functionalRequirements as $fr) {
            if ($this->functionalRequirementInput->frId != $fr->id) {
                $frInputs = FunctionalRequirementInput::where([
                ['frId',$fr->id],
                ['tableName',$table->getName()],
                ['columnName',$column->getName()],
                ])->get();

                if (count($frInputs) > 0) {
                    $foundOther = true;
                    break;
                }
            }
        }

        if ($foundOther) {
            return [];
        }

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
        foreach ($records as $index => $record) {
            $oldValues[] = $record[$this->functionalRequirementInput->columnName];
            unset($records[$index][$this->functionalRequirementInput->columnName]);
        }
        $result[$tableName] = [];
        if ($table->isFK($column->getName())) {
            foreach ($table->getAllFK() as $fk) {
                foreach ($fk->getColumns() as $link) {
                    if ($link['from']['tableName'] == $tableName && $link['from']['columnName'] == $columnName) {
                        $fkDelete[] = [
                            'tableName' => $tableName,
                            'info' => $fk
                        ];
                    }
                }
            }
            //dd($fkDelete);
        }
        if ($table->isUnique($column->getName())) {
            foreach ($this->database->findUniqueConstraintRelated($tableName, $columnName) as $unique) {
                if (count($unique->getColumns()) > 1) {
                    $cckDelete[] = [
                            'tableName' => $tableName,
                            'info' => $unique
                        ];
                }
            }
        }

        $result[$tableName][$columnName] = [
                    'changeType' => 'delete',
                    'old' => $refSchema,
                    'new' => [],
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

}
