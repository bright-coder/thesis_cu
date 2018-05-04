<?php

namespace App\Library\State\AnalyzeDBMethod;

use App\Model\ChangeRequestInput;
use App\Library\Database\Database;
use App\Library\CustomModel\DBTargetInterface;

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

    /**
    * @var FunctionalRequirementInput;
    */
    protected $functionalRequirementInput = null;

    public function isSchemaImpact(): bool { return $this->schemaImpact; }
    public function isInstanceImpact() : bool {
        return count($this->instanceImpactResult) > 0; 
    }

    public function getInstanceImpactResult(): array {
        $this->instanceImpactResult = array_unique($this->instanceImpactResult, SORT_REGULAR); 
        return $this->instanceImpactResult;
    }
    
    public function getSchemaImpactResult(): array {
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

    abstract public function analyze(): bool;
    abstract public function modify(): bool;
    
    
}
