<?php

namespace App\Library\State\AnalyzeDBMethod\TypeEditAnalysis;

abstract class ColumnAnalysis
{
    abstract public function analyzeColumn();

    protected function findFunctionalRequirementById(string $id) : FunctionalRequirement
    {
        return FunctionalRequirement::where('id', $id)->first();
    }

    protected function isChangeRequestInputUnique() : bool
    {
        \strcasecmp($this->changeRequestInput->unique, 'N') == 0 ? false : true ;
    }

    protected function isChangeRequestInputNullable() : bool
    {
        \strcasecmp($this->changeRequestInput->nullable, 'N') == 0 ? false : true ;
    }

    protected function getPK(string $tableName): Constraint
    {
        $table = $this->database->getTableByName($tableName);
        return $table->getPK();
    }

    protected function getFK(string $tableName, string $columnName): Constraint
    {
        $table = $this->database->getTableByName($tableName);
        $fks = $table->getAllFK();
        foreach ($fks as $fk) {
            foreach ($fk->getColumns() as $link) {
                if ($link['from']['columnName'] == $columnName) {
                    return $fk;
                }
            }
        }
    }

    protected function isDBColumnUnique(string $tableName, string $columnName): bool
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

    protected function isPK(string $tableName, string $columnName): bool
    {
        $table = $this->database->getTableByName($tableName);
        $pk = $table->getPK();
        return \in_array($columnName, $pk->getColumns());
    }

    protected function isFK(string $tableName, string $columnName): bool
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

    protected function findInstanceImpactByDataType(string $changeDataType, string $dbColumnDataType): bool
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

    protected function findInstanceImpactByLength(int $changeLength, int $dbColumnLength): bool
    {
        return $changeLength >= $dbColumnLength ? false : true;
    }

    protected function findInstanceImpactByPrecision(int $changePrecision, int $dbPrecision): bool
    {
        return $changePrecision >= $dbPrecision ? false : true;
    }

    protected function findInstanceImpactByScale(int $changeScale, int $dbColumnScale): bool
    {
        return $changeScale >= $dbColumnScale ? false : true;
    }

    protected function findInstanceImpactByNullable(bool $changeNullable, bool $dbColumnNullable): bool
    {
        return ($changeNullable === false && $dbColumnNullable === true) ? true : false;
    }

    protected function findInstanceImpactByUnique(bool $changeUnique, bool $dbColumnUnique): bool
    {
        return ($changeUnique === true && $dbColumnUnique === false) ? true : false;
    }

    protected function findInstanceImpactByMin($changeMin, $columnMin): bool
    {
        if ($columnMin == null) {
            return true;
        }

        return ($changeMin > $columnMin) ? true : false;
    }

    protected function getMin(string $tableName, string $columnName)
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

    protected function getMax(string $tableName, string $columnName)
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

    protected function findInstanceImpactByMax($changeMax, $columnMax): bool
    {
        if ($columnMax == null) {
            return true;
        }

        return ($changeMax < $columnMax) ? true : false;
    }

    protected function isStringType(string $dataType) : bool
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

    protected function isNumericType(string $dataType) : bool
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

    protected function isFloatType(string $dataType) : bool
    {
        switch (\strtolower($dataType)) {
            case 'float':
            case 'decimal':
                return true;
            
            default:
                return false;
        }
    }
}