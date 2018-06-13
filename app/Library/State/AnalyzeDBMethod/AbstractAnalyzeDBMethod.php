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
    * @var FunctionalRequirementInput;
    */
    protected $functionalRequirementInput = null;

    protected function getFRInputById(string $frId) : FunctionalRequirementInput
    {
        return FunctionalRequirementInput::where('id',$frId)->first();
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

    protected function findFKRelated(string $tableName, string $columnName) : array
    {
        $result = [];
        foreach ($this->database->getAllTables() as $table) {
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

    abstract public function analyze(): array;
}
