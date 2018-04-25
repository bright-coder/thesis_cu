<?php

namespace App\Library\State\AnalyzeDBMethod;

use App\Library\State\AnalyzeDBMethod\AbstractAnalyzeDBMethod;
use App\Model\ChangeRequestInput;
use App\Library\Database\Database;
use App\Library\CustomModel\DBTargetConnection;
use App\Library\CustomModel\DBTargetInterface;
use App\Model\FunctionalRequirement;
use App\Library\Node;

class AnalyzeDBEdit extends AbstractAnalyzeDBMethod
{
    
    /**
     * Undocumented variable
     *
     * @var FunctionalRequirement;
     */
    private $functionalRequirement = null;

    public function construct(Database $database, ChangeRequestInput $changeRequestInput, DBTargetInterface $dbTargetConnection, string $frId)
    {
        $this->database = $database;
        $this->changeRequestInput = $changeRequestInput;
        $this->dbTargetConnection = $dbTargetConnection;
        $this->functionalRequirment = $this->findFunctionalRequirementById($frId);
    }

    public function analyze() : bool
    {
        $this->schemaImpact = true;
        $this->InstanceImpact = true;
        
        $table = $this->database->getTableByName($this->changeRequestInput->tableName);
        $column = $table->getColumnbyName($this->changeRequestInput->columnName);

        $dataTypeRef = $column->getDataType()->dataType();

        if ($this->database->isLinked($table->getName(), $column->getName())) {

            // Node of Primary Column
            $primaryColumn = $this->findPrimaryColumn($table->getName(), $column->getName());
        }


        if ($this->changeRequestInput->dataType != null) {
            if ($this->findInstanceImpactByDataType($this->changeRequestInput->dataType, $column->getDataType()->dataType())) {
                return true;
            }
            $dataTypeRef = $this->changeRequestInput->dataType;
        }

        if ($this->findInstanceImpactByDataTypeDetail($dataTypeRef)) {
            return true;
        }
        
        if ($this->changeRequestInput->nullable != null) {
            if ($this->findInstanceImpactByNullable(\strtoupper($this->changeRequestInput->nullable) == 'N' ? false : true, $column->isNullable())) {
                return true;
            }
        }
        
        if ($this->chageRequestInput->unqiue != null) {
            if ($this->findInstanceImpactByUnique($this->isUnique, $this->isDBColumnUnique($table->getName(), $column->getName()))) {
                return true;
            }
        }

        return false;
    }

    private function findImpactAnotherTable(Node $primaryColumn) : array
    {
        $travel = $primaryColumn;
        $listImpact = [
            ['tableName' => $node->getTableName(), 'columnName' => $node->getColumnName]
        ];
        while (count($travel->getLinks()) > 0) {
            foreach ($travel->getLinks() as $node) {
                $listImpact = ['tableName' => $node->getTableName(), 'columnName' => $node->getColumnName];
                if (count($node->getLinks()) > 0) {
                    $listImpact[] = $this->findImpactAnotherTable($node->getTableName(), $node->getColumName());
                }
            }
        }
        return $listImpact;
    }

    // return [tableName][columnName];
    private function findPrimaryColumn(string $tableName, string $columnName) : Node
    {
        $node = $this->database->getHashFks()[$tableName][$columnName];
        $travel = $node;
        while ($travel->getPrevious() != null) {
            $travel = $travel->getPrevious();
        }

        return $travel;
    }

    private function isDBColumnUnique(string $tableName, string $columnName): bool
    {
        $uniqueConstraints = $this->database->getTableByName($tableName)->getAllUniqueConstraint();
        foreach ($uniqueConstraints as $uniqueConstraint) {
            foreach ($uniqueConstraint->getColumns() as $column) {
                if ($column == $columnName) {
                    return true;
                }
            }
        }
        return false;
    }

    private function findInstanceImpactByDataTypeDetail(string $dataType) : bool
    {
        $table = $this->database->getTableByName($this->changeRequestInput->tableName);
        $column = $table->getColumnbyName($this->changeRequestInput->columnName);

        if ($this->isStringType($dataType)) {
            if ($this->changeRequestInput->length != null) {
                if ($this->findInstanceImpactByLength($this->changeRequestInput->length, $column->getDataType()->getLength())) {
                    $instance = $this->dbTargetConnection->getInstanceByTableName($table->getName(), "LEN({$column->getName()}) > {$this->changeRequestInput->length}");
                    if (count($instance) > 0) {
                        $this->instanceImpactResult[] = $instance;
                        return true;
                    }
                }
            }
        } elseif ($this->isNumericType($dataType)) {
            if ($this->changeRequestInput->min != null && $this->changeRequestInput->min != '') {
                if ($this->findInstanceImpactByMin($this->changeRequestInput->min, $this->getMin($table->getName(), $column->getName()))) {
                    $instance = $this->dbTargetConnection->getInstanceByTableName($table->getName(), "{$column->getName()} < {$this->changeRequestInput->min}");
                    if (count($instance) > 0) {
                        $this->instanceImpactResult[] = $instance;
                        return true;
                    }
                }
            }
            if ($this->changeRequestInput->max != null && $this->changeRequestInput->max != '') {
                if ($this->findInstanceImpactByMax($this->changeRequestInput->max, $this->getMax($table->getName(), $column->getName()))) {
                    $instance = $this->dbTargetConnection->getInstanceByTableName($table->getName(), "{$column->getName()} > {$this->changeRequestInput->max}");
                    if (count($instance) > 0) {
                        $this->instanceImpactResult[] = $instance;
                        return true;
                    }
                }
            }
        } elseif ($this->isFloatType($dataType)) {
            if ($this->changeRequestInput->precision != null) {
                if ($this->findInstanceImpactByPrecision($this->changeRequestInput->precision, $column->getDataType()->getPrecision())) {
                    $instance = $this->dbTargetConnection->getInstanceByTableName($table->getName(), "LEN({$column->getName()})-1 > {$this->changeRequestInput->precision}");
                    if (count($instance) > 0) {
                        $this->instanceImpactResult[] = $instance;
                        return true;
                    }
                }
            }
            if (\strtolower($dataType) == 'decimal') {
                if ($this->changeRequestInput->scale != null) {
                    if ($this->findInstanceImpactByScale($this->changeRequestInput->scale, $column->getDataType()->getScale())) {
                        $instance = $this->dbTargetConnection->getInstanceByTableName($table-getName(), "LEN(SUBSTRING({$column->getName()},CHARINDEX('.', {$column->getName()})+1, 4000)) > {$this->changeRequestInput->scale}");
                        if (count($instance) > 0) {
                            $this->instanceImpactResult[] = $instance;
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    public function modify(DBTargetConnection $dbTargetConnection): bool
    {
        //$dbTargetConnection->addColumn($changeRequestInput);
    }

    private function findFunctionalRequirementById(string $id) : FunctionalRequirement
    {
        return FunctionalRequirement::where('id', $id)->first();
    }

    private function findInstanceImpactByDataType(string $changeDataType, string $dbColumnDataType): bool
    {
        $char = ['varchar', 'char'];
        $unicodeChar = ['nvarchar', 'nchar'];
        if (array_search($changeDataType, $char) && array_search($dbColumnDataType, $char)) {
            return false;
        }
        if (array_search($changeDataType, $unicodeChar) && array_search($dbColumnDataType, $unicodeChar)) {
            return false;
        }
        if ($changeDataType == $dbColumnDataType) {
            return false;
        }

        return true;
    }

    private function findInstanceImpactByLength(int $changeLength, int $dbColumnLength): bool
    {
        return $changeLength >= $dbColumnLength ? false : true;
    }

    private function findInstanceImpactByPrecision(int $changePrecision, int $dbPrecision): bool
    {
        return $changePrecision >= $dbPrecision ? false : true;
    }

    private function findInstanceImpactByScale(int $changeScale, int $dbColumnScale): bool
    {
        return $changeScale >= $dbColumnScale ? false : true;
    }

    private function findInstanceImpactByNullable(bool $changeNullable, bool $dbColumnNullable): bool
    {
        return ($changeNullable === false && $dbColumnNullable === true) ? true : false;
    }

    private function findInstanceImpactByUnique(bool $changeUnique, bool $dbColumnUnique): bool
    {
        return ($changeUnique === true && $dbColumnUnique === false) ? true : false;
    }

    private function findInstanceImpactByMin($changeMin, $columnMin): bool
    {
        if ($columnMin == null) {
            return true;
        }

        return ($changeMin > $columnMin) ? true : false;
    }

    private function getMin(string $tableName, string $columnName)
    {
        $checkConstraints = $this->database->getTableByName($tableName)->getAllCheckConstraint();
        foreach ($checkConstraints as $checkConstraint) {
            foreach ($checkConstraint->getColumns() as $column) {
                if ($column == $columnName) {
                    $minAllColumn = $checkConstraint->getDetail()['min'];
                    if (\array_key_exists($columnName, $minAllColumn)) {
                        return $minAllColumn[$columnName];
                    }
                }
            }
        }
        return null;
    }

    private function getMax(string $tableName, string $columnName)
    {
        $checkConstraints = $this->database->getTableByName($tableName)->getAllCheckConstraint();
        foreach ($checkConstraints as $checkConstraint) {
            foreach ($checkConstraint->getColumns() as $column) {
                if ($column == $columnName) {
                    $minAllColumn = $checkConstraint->getDetail()['max'];
                    if (\array_key_exists($columnName, $minAllColumn)) {
                        return $minAllColumn[$columnName];
                    }
                }
            }
        }
        return null;
    }

    private function findInstanceImpactByMax($changeMax, $columnMax): bool
    {
        if ($columnMax == null) {
            return true;
        }

        return ($changeMax < $columnMax) ? true : false;
    }

    private function isStringType(string $dataType) : bool
    {
        switch (\strtolower($dataType)) {
            case 'char':
            case 'varchar':
            case 'nchar':
            case 'nvarchar':
                return true;
        
            default:
                return false;
        }
    }

    private function isNumericType(string $dataType) : bool
    {
        switch (\strtolower($dataType)) {
            case 'int':
            case 'float':
            case 'decimal':
                return true;

            default:
                return false;
        }
    }

    private function isFloatType(string $dataType) : bool
    {
        switch (\strtolower($dataType)) {
            case 'float':
            case 'decimal':
                return true;
            
            default:
                return false;
        }
    }

    private function isPK(string $tableName, string $columnName): bool
    {
        $table = $this->database->getTableByName($tableName);
        $pk = $table->getPK();
        return \in_array($columnName, $pk->getColumns());
    }

    private function isFK(string $tableName, string $columnName): bool
    {
        $table = $this->database->getTableByName($tableName);
        $fks = $table->getAllFK();
        foreach ($fks as $fk) {
            foreach ($fk->getColumns() as $link) {
                if ($link['from']['columnName'] == $columnName) {
                    return true;
                }
            }
        }
        return false;
    }
}
