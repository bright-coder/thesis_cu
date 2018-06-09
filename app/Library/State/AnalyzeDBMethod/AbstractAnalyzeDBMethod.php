<?php

namespace App\Library\State\AnalyzeDBMethod;

use App\Model\ChangeRequestInput;
use App\Library\Database\Database;
use App\Library\CustomModel\DBTargetInterface;
use App\Model\FunctionalRequirementInput;
use App\Library\Constraint\Constraint;

abstract class AbstractAnalyzeDBMethod
{
    /**
     * Undocumented variable
     *
     * @var ChangeRequestInput
     */
    protected $changeRequestInput = null ;
    /**
     * Undocumented variable
     *
     * @var Database
     */
    protected $database = null;

    /**
     * Undocumented variable
     *
     * @var DBTargetInterface
     */
    protected $dbTargetConnection = null;

    /**
     * @var array
     */
    protected $instanceImpactResult = [];
    protected $schemaImpactResult = [];
    protected $keyConstraintImpactResult = [];

    /**
    * @var FunctionalRequirementInput;
    */
    protected $functionalRequirementInput = null;

    public function isSchemaImpact(): bool
    {
        return $this->schemaImpact;
    }
    public function isInstanceImpact() : bool
    {
        return count($this->instanceImpactResult) > 0;
    }

    public function getInstanceImpactResult(): array
    {
        //$this->instanceImpactResult = array_unique($this->instanceImpactResult, SORT_REGULAR);
        return $this->instanceImpactResult;
    }
    
    public function getSchemaImpactResult(): array
    {
        return $this->schemaImpactResult;
    }

    protected function findFunctionalRequirementInputById(string $id) : FunctionalRequirementInput
    {
        return FunctionalRequirementInput::where('id', $id)->first();
    }

    protected function findUniqueConstraintRelated(string $tableName, string $columnName): array
    {
        $uniqueConstraints = $this->database->getTableByName($tableName)->getAllUniqueConstraint();
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

    protected function findCheckConstraintRelated(string $tableName, string $columnName): array
    {
        $checkConstraints = $this->database->getTableByName($tableName)->getAllCheckConstraint();
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

    protected function addSchemaImpactResult(string $tableName, string $columnName, string $changeType, array $old, array $new) : void
    {
        if (!array_key_exists($tableName, $this->schemaImpactResult)) {
            $this->schemaImpactResult[$tableName] = [
                'tableName' => $tableName,
                'columnList' => []
            ];
        }
        if (!array_key_exists($columnName, $this->schemaImpactResult[$tableName]['columnList'])) {
            $this->instanceImpactResult[$tableName]['columnList'] = [];
        }
        $this->instanceImpactResult[$tableName]['columnList'][$columnName] = [
            'columnName' => $columnName,
            'changeType' => $changeType,
            'old' => $old,
            'new' => $new
        ];
    }

    protected function addInstanceImpactResult(string $tableName, string $columnName, array $oldRecords, array $newValues) : void
    {
        if (!array_key_exists($tableName, $this->instanceImpactResult)) {
            $this->instanceImpactResult[$tableName] = [
                'tableName' => $tableName,
                'columnList' => []
            ];
        }
        if (!array_key_exists($columnName, $this->instanceImpactResult[$tableName]['columnList'])) {
            $this->instanceImpactResult[$tableName]['columnList'] = [];
        }
        $this->instanceImpactResult[$tableName]['columnList'][$columnName] = [
            'oldInstance' => $oldRecords,
            'newInstance' => $newValues
        ];
    }

    abstract public function analyze(): array;
    abstract public function modify(): bool;
}
