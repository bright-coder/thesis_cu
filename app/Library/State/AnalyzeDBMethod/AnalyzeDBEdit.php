<?php

namespace App\Library\State\AnalyzeDBMethod;

use App\Library\State\AnalyzeDBMethod\AbstractAnalyzeDBMethod;
use App\Model\ChangeRequestInput;
use App\Library\Database\Database;
use App\Library\CustomModel\DBTargetConnection;
use App\Library\CustomModel\DBTargetInterface;
use App\Model\FunctionalRequirement;
use App\Model\FunctionalRequirementInput;
use App\Library\Node;
use App\Library\Constraint\Constraint;

class AnalyzeDBEdit extends AbstractAnalyzeDBMethod
{
    
    /**
     * Undocumented variable
     *
     * @var FunctionalRequirement;
     */
    private $functionalRequirement = null;
    /**
     * Undocumented function
     *
     * @var FunctionalRequirementInput;
     */
    private $functionalRequirementInput = null;

    public function __construct(Database $database, ChangeRequestInput $changeRequestInput, DBTargetInterface $dbTargetConnection, string $frId)
    {
        $this->database = $database;
        $this->changeRequestInput = $changeRequestInput;
        $this->dbTargetConnection = $dbTargetConnection;
        $this->functionalRequirement = $this->findFunctionalRequirementById($frId);
        $this->functinoalRequirementInput = $this->findFunctionalRequirementInputById($changeRequestInput->functionalRequirementInputId);
    }

    public function analyze() : bool
    {
        $this->schemaImpact = true;
        //dd($this->functionalRequirement);
        $table = $this->database->getTableByName($this->functinoalRequirementInput->tableName);
        $column = $table->getColumnbyName($this->functinoalRequirementInput->columnName);

        // $this->schemaImpactResult[] = ['tableName' => $this->functinoalRequirementInput->tableName,
        // 'columnName' => $this->functinoalRequirementInput->columnName];

        $dataTypeRef = $column->getDataType()->getType();

        if ($this->database->isLinked($table->getName(), $column->getName())) {

            // If this column is not Primary column ;
            if ($this->isFK($table->getName(), $column->getName())) {
                if ($this->changeRequestInput->unique != null) {
                    if ($this->isUnique()) {
                        $duplicateInstance = $this->dbTargetConnection->getDuplicateInstance($table->getName(), [$column->getName()]);
                        if (count($duplicateInstance > 0)) {
                            // cannot modify impact; Referential Integrity;
                        }
                    }
                }
                if ($this->changeRequestInput->nullable != null) {
                    if (! $this->isNullable()) {
                        $nullInstance =  $this->dbTargetConnection->getInstanceByTableName($table->getName(), "{$column->getName()} IS NULL");
                        if (count($nullInstance) > 0) {
                            // cannot modify impact; Referential Integrity;
                        }
                    }
                }
                if ($this->changeRequestInput->default != null) {
                    //
                }
            }


            // use Primary Column to find impacted
            // This Impact will affected many table;
            $primaryColumnNode = $this->findPrimaryColumnNode($table->getName(), $column->getName());
            $primaryTable = $this->database->getTableByName($primaryColumnNode->getTableName());
            $primaryColumn = $primaryTable->getColumnByName($primaryColumnNode->getColumnName());
            $instance = array();
            $dataTypeRef = $primaryColumn->getDataType()->getType();
            if ($this->changeRequestInput->dataType != null) {
                if ($this->findInstanceImpactByDataType($this->changeRequestInput->dataType, $dataTypeRef)) {
                    $instance[] = $this->dbTargetConnection->getInstanceByTableName($primaryTable->getName());
                }

                $dataTypeRef = $this->changeRequestInput->dataType;
            }

            //$instance = ;

            if ($this->changeRequest->min != null) {
                $primaryColumnMin = $this->getMin($primaryTable->getName(), $primaryColumn->getName());
                $instance[] = $this->findInstanceImpactByMin($this->changeRequest->min, $primaryColumnMin);
            }

            if ($this->changeRequest->max != null) {
                $primaryColumnMax = $this->getMax($primaryTable->getName(), $primaryColumn->getName());
                $instance[] = $this->findInstanceImpactByMax($this->changeRequest->max, $primaryColumnMax);
            }
        }


        if ($this->changeRequestInput->dataType != null) {
            if ($this->findInstanceImpactByDataType($this->changeRequestInput->dataType, $column->getDataType()->getType())) {
                $this->instanceImpactResult[] = $this->dbTargetConnection->getInstanceByTableName($this->functinoalRequirementInput->tableName);
                return false;
            }
            
            $dataTypeRef = $this->changeRequestInput->dataType;
        }

        $this->findInstanceImpactByDataTypeDetail($dataTypeRef);
        

        return false;
    }

    private function findImpactNormalColumn()
    {

        $table = $this->database->getTableByName($this->functionalRequirementInput->tableName);
        $column = $table->getColumnByName($this->functionRequirementInput->ColumnName);
        
        //set refSchema to oldSchema
        $refSchema = [
            'dataType' => $column->getDataType()->getType(),
            'length' => $column->getDataType()->getLength(),
            'precision' => $column->getDataType()->getPrecision(),
            'scale' => $column->getDataType()->getScale(),
            'default' => $column->getDefault(),
            'nullalble' => $column->isNullable(),
            'unique' => $this->isDBColumnUnique($table->getName(), $column->getName()),
            'min' => $this->getMin($table->getName(), $column->getName()),
            'max' => $this->getMax($table->getName(), $column->getName())
        ];

        $newSchema = array_filter($this->changeRequestInput->toArray(),function ($val){
            return $val !== NULL;
        });

        $this->schemaImpactResult[0] = [
            'tableName' => $table->getName(),
            'columnName' => $column->getName(),
            'oldSchema' => $refSchema,
            'newSchema' => count($newSchema > 0) ? $newSchema : null
        ];

        $dataTypeRef = $refSchema['dataType'];

        $this->instanceImpactResult[0] = array();
        if ($this->changeRequestInput->dataType != null) {

            if ($this->findInstanceImpactByDataType($this->changeRequestInput->dataType, $refSchema['dataType'])) {
                $instance = $this->dbTargetConnection->getInstanceByTableName($this->functinoalRequirementInput->tableName);
                $this->instanceImpactResult[] = $instance;
                //return false;
            }
            $refSchema['dataType'] = $this->changeRequestInput->dataType;
            $dataTypeRef = $this->changeRequestInput->dataType;
        }

        if ($this->isStringType($dataTypeRef)) {
            if ($this->changeRequestInput->length != null) {

                if ($this->findInstanceImpactByLength($this->changeRequestInput->length, $column->getDataType()->getLength())) {
                    $instance = $this->dbTargetConnection->getInstanceByTableName($table->getName(), "LEN({$column->getName()}) > {$this->changeRequestInput->length}");
                    if (count($instance) > 0) {
                        array_push($this->instanceImpactResult[0],$instance);
                    }
                }
                $refSchema['length'] = $this->changeRequestInput->length;
            }
        } elseif ($this->isNumericType($dataTypeRef)) {
            
            if ($this->changeRequestInput->min != null && $this->changeRequestInput->min != '') {
                if ($this->findInstanceImpactByMin($this->changeRequestInput->min, $refSchema['min'])) {
                    $instance = $this->dbTargetConnection->getInstanceByTableName($table->getName(), "{$column->getName()} < {$this->changeRequestInput->min}");
                    if (count($instance) > 0) {
                        array_push($this->instanceImpactResult[0],$instance);
                    }
                }
            }
            if ($this->changeRequestInput->max != null && $this->changeRequestInput->max != '') {
                if ($this->findInstanceImpactByMax($this->changeRequestInput->max, $refSchema['max'])) {
                    $instance = $this->dbTargetConnection->getInstanceByTableName($table->getName(), "{$column->getName()} > {$this->changeRequestInput->max}");
                    if (count($instance) > 0) {
                        array_push($this->instanceImpactResult[0],$instance);
                    }
                }
            }

            if ($this->changeRequestInput->min == '') {
                $refSchema['min'] = null;
            }

            if ($this->changeRequestInput->max == '') {
                $refSchema['max'] = null;
            }

        } elseif ($this->isFloatType($dataTypeRef)) {

            if ($this->changeRequestInput->precision != null) {
                if ($this->findInstanceImpactByPrecision($this->changeRequestInput->precision, $refSchema['precision'])) {
                    $instance = $this->dbTargetConnection->getInstanceByTableName($table->getName(), "LEN({$column->getName()})-1 > {$this->changeRequestInput->precision}");
                    if (count($instance) > 0) {
                        array_push($this->instanceImpactResult[0],$instance);
                    }
                }
                $refSchema['precision'] = $this->changeRequestInput->precision;
            }
            if (\strtolower($dataType) == 'decimal') {
                if ($this->changeRequestInput->scale != null) {
                    if ($this->findInstanceImpactByScale($this->changeRequestInput->scale, $refSchema['scale'])) {
                        $instance = $this->dbTargetConnection->getInstanceByTableName($table-getName(), "LEN(SUBSTRING({$column->getName()},CHARINDEX('.', {$column->getName()})+1, 4000)) > {$this->changeRequestInput->scale}");
                        if (count($instance) > 0) {
                            array_push($this->instanceImpactResult[0],$instance);
                        }
                    }
                }
                $refSchema['scale'] = $this->changeRequestInput->scale;
            }
        }

        if($this->changeRequestInput->default != null) {
            $refSchema['default'] = $this->changeRequestInput->default;
        }

        if ($this->changeRequestInput->nullable != null) {

            if ($this->findInstanceImpactByNullable($newColumnSchema['nullable'], $refSchema['nullable'])) {
                $instance = $this->dbTargetConnection->getInstanceByTableName($table->getName(), "{$column->getName()} IS NULL");
                if (count($instane) > 0) {
                    array_push($this->instanceImpactResult[0],$instance);
                }
            }
            $refSchema['nullable'] = \strcasecmp($this->changeRequestInput->nullable, 'N') == 0 ? false : true;
        }
        
        if ($this->changeRequestInput->unqiue != null) {

            if ($this->findInstanceImpactByUnique($newColumnSchema['unique'], $refSchema['unique'] )) {
                $instance = $this->dbTargetConnection->getDuplicateInstance($table->getName(), $column->getName());
                if (count($instance) > 0) {
                    array_push($this->instanceImpactResult[0],$instance);
                }
            }
            $refSchema['unique'] = \strcasecmp($this->changeRequestInput->unique, 'N') == 0 ? false : true;
        }
        
        
        if(count($this->instanceImpactResult[0]) > 0) {
            $numRows = $this->dbTargetConnection->getNumRows($table->getName());
            $randomData  = RandomContext::getRandomData($numRows, $refSchema['dataType'],
            [
                'length' => $refSchema['length'],
                'precision' => $refSchema['precision'],
                'scale' => $refSchema['scale'],
                'min' => $refSchema['min'],
                'max' => $refSchema['max']
            ], 
            $refSchema['unique']
        );

        $this->instanceImpactResult[0] = array_unique($this->instanceImpactResult[0], SORT_REGULAR);

        if(count($randomData) >= count($this->instanceImpactResult[0])) {
            $randomData = \array_splice($randomData,0, count($this->instanceImpactResult[0]));
        }
        $this->instanceImpactResult[0] = [
            'oldInstance' =>  $this->instanceImpactResult[0],
            'newInstance' => $randomData
        ];
        //$pkColumns = $this->database->getTableByName($this->changeRequestInput->tableName)->getPK()->getColumns();
        }
        else {
            $this->instanceImpactResult[0] = null;
        }

    }

    public function modify(): bool
    {
        //$dbTargetConnection->addColumn($changeRequestInput);
        foreach($this->schemaImpactResult as $index => $scResult) {
            $this->dbTargetConnection->disableConstraint();
        }

        return true;
    }

    private function findFunctionalRequirementById(string $id) : FunctionalRequirement
    {
        return FunctionalRequirement::where('id', $id)->first();
    }

    private function isChangeRequestInputUnique() : bool
    {
        \strcasecmp($this->changeRequestInput->unique, 'N') == 0 ? false : true ;
    }

    private function isChangeRequestInputNullable() : bool
    {
        \strcasecmp($this->changeRequestInput->nullable, 'N') == 0 ? false : true ;
    }

    private function getPK(string $tableName): Constraint
    {
        $table = $this->database->getTableByName($tableName);
        return $table->getPK();
    }

    private function getFK(string $tableName, string $columnName): Constraint
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
}
