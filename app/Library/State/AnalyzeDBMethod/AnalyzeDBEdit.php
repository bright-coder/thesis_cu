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

        if($this->database->isLinked($table->getName(), $column->getName())) {

            // If this column is not Primary column ;
            if($this->isFK($table->getName(), $column->getName())) {
                if($this->changeRequestInput->unique != null) {
                    if($this->isUnique()) {
                        $duplicateInstance = $this->dbTargetConnection->getDuplicateInstance($table->getName(),[$column->getName()]);
                        if(count($duplicateInstance > 0)) {
                            // cannot modify impact; Referential Integrity;
                        }
                    }
                }
                if($this->changeRequestInput->nullable != null) {
                    if( ! $this->isNullable()) {
                        $nullInstance =  $this->dbTargetConnection->getInstanceByTableName($table->getName(),"{$column->getName()} IS NULL");
                        if(count($nullInstance) > 0) {
                            // cannot modify impact; Referential Integrity; 
                        }
                    }
                }
                if($this->changeRequestInput->default != null) {
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
            if($this->changeRequestInput->dataType != null) {
                
                if($this->findInstanceImpactByDataType($this->changeRequestInput->dataType, $dataTypeRef) ) {
                    $instance[] = $this->dbTargetConnection->getInstanceByTableName($primaryTable->getName());
                }

                $dataTypeRef = $this->changeRequestInput->dataType;
            }

            //$instance = ;

            if($this->changeRequest->min != null) {
                $primaryColumnMin = $this->getMin($primaryTable->getName(),$primaryColumn->getName());
                $instance[] = $this->findInstanceImpactByMin($this->changeRequest->min, $primaryColumnMin);
            }

            if($this->changeRequest->max != null) {
                $primaryColumnMax = $this->getMax($primaryTable->getName(), $primaryColumn->getName());
                $instance[] = $this->findInstanceImpactByMax($this->changeRequest->max, $primaryColumnMax);
            }

        }


        if ($this->changeRequestInput->dataType != null) {
            if($this->findInstanceImpactByDataType($this->changeRequestInput->dataType, $column->getDataType()->getType()) ) {
                $this->instanceImpactResult[] = $this->dbTargetConnection->getInstanceByTableName($this->functinoalRequirementInput->tableName);
                return false;
            }
            
            $dataTypeRef = $this->changeRequestInput->dataType;
        }

        $this->findInstanceImpactByDataTypeDetail($dataTypeRef);
        
        if ($this->changeRequestInput->nullable != null) {
            $this->findInstanceImpactByNullable(\strtoupper($this->changeRequestInput->nullable) == 'N' ? false : true, $column->isNullable());
        }
        
        if ($this->changeRequestInput->unqiue != null) {
            $this->findInstanceImpactByUnique($this->isUnique(), $this->isDBColumnUnique($table->getName(), $column->getName()));
        }

        return false;
        
    }


    private function findInstanceImpactByDataTypeDetail(string $tableName, string $columnName, string $dataType = null) : array
    {
        $table = $this->database->getTableByName($tableName);
        $column = $table->getColumnbyName($columnName);
        $dataType = $column->getDataType()->getType();

        if ($this->isStringType($dataType)) {
            if ($this->changeRequestInput->length != null) {
                if ($this->findInstanceImpactByLength($this->changeRequestInput->length, $column->getDataType()->getLength())) {
                    $instance = $this->dbTargetConnection->getInstanceByTableName($table->getName(), "LEN({$column->getName()}) > {$this->changeRequestInput->length}");
                    if (count($instance) > 0) {
                        $this->instanceImpactResult[] = $instance;
                        
                    }
                }
            }
        } elseif ($this->isNumericType($dataType)) {
            if ($this->changeRequestInput->min != null && $this->changeRequestInput->min != '') {
                if ($this->findInstanceImpactByMin($this->changeRequestInput->min, $this->getMin($table->getName(), $column->getName()))) {
                    $instance = $this->dbTargetConnection->getInstanceByTableName($table->getName(), "{$column->getName()} < {$this->changeRequestInput->min}");
                    if (count($instance) > 0) {
                        $this->instanceImpactResult[] = $instance;
                        
                    }
                }
            }
            if ($this->changeRequestInput->max != null && $this->changeRequestInput->max != '') {
                if ($this->findInstanceImpactByMax($this->changeRequestInput->max, $this->getMax($table->getName(), $column->getName()))) {
                    $instance = $this->dbTargetConnection->getInstanceByTableName($table->getName(), "{$column->getName()} > {$this->changeRequestInput->max}");
                    if (count($instance) > 0) {
                        $this->instanceImpactResult[] = $instance;
                        
                    }
                }
            }
        } elseif ($this->isFloatType($dataType)) {
            if ($this->changeRequestInput->precision != null) {
                if ($this->findInstanceImpactByPrecision($this->changeRequestInput->precision, $column->getDataType()->getPrecision())) {
                    $instance = $this->dbTargetConnection->getInstanceByTableName($table->getName(), "LEN({$column->getName()})-1 > {$this->changeRequestInput->precision}");
                    if (count($instance) > 0) {
                        $this->instanceImpactResult[] = $instance;
                        
                    }
                }
            }
            if (\strtolower($dataType) == 'decimal') {
                if ($this->changeRequestInput->scale != null) {
                    if ($this->findInstanceImpactByScale($this->changeRequestInput->scale, $column->getDataType()->getScale())) {
                        $instance = $this->dbTargetConnection->getInstanceByTableName($table-getName(), "LEN(SUBSTRING({$column->getName()},CHARINDEX('.', {$column->getName()})+1, 4000)) > {$this->changeRequestInput->scale}");
                        if (count($instance) > 0) {
                            $this->instanceImpactResult[] = $instance;
                            
                        }
                    }
                }
            }
        }

        return false;
    }

    public function modify(): bool
    {
        //$dbTargetConnection->addColumn($changeRequestInput);
        return true;
    }

    private function findFunctionalRequirementById(string $id) : FunctionalRequirement
    {
        return FunctionalRequirement::where('id', $id)->first();
    }
    
}
