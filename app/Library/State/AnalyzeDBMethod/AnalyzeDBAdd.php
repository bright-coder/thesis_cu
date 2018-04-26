<?php

namespace App\Library\State\AnalyzeDBMethod;
use App\Library\State\AnalyzeDBMethod\AbstractAnalyzeDBMethod;
use App\Model\ChangeRequestInput;
use App\Library\Database\Database;
use App\Library\CustomModel\DBTargetInterface;
use App\Library\Random\RandomContext;

class AnalyzeDBAdd extends AbstractAnalyzeDBMethod {

    public function __construct(Database $database, ChangeRequestInput $changeRequestInput, DBTargetInterface $dbTargetConnection)
    {
        $this->database = $database;
        $this->changeRequestInput = $changeRequestInput;
        $this->dbTargetConnection = $dbTargetConnection;

    }

    public function analyze(): bool {
        
        $table = $database->getTableByName($changeRequestInput->tableName);

        // if not have column in table
        if(!$table->getColumnbyName($changeRequestInput->columnName)) {
            $this->instanceImpact = true;
            return true;
        }
        return false;
    }

    public function modify(DBTargetInterface $dbTargetConnection): bool {
        $dbTargetConnection->addColumn($changeRequestInput);
        
        if($this->isUnique()) 
            $dbTargetConnection->addUniqueConstraint($this->changeRequestInput->tableName,$this->changeRequestInput->columnName);
        
        if($this->changeRequestInput->min != null || $this->changeRequestInput->max != null)
            switch ($this->changeRequestInput->dataType) {
                case 'int':
                case 'float':
                case 'decimal' :
                    $dbTargetConnection->addCheckConstraint(
                        $this->changeRequestInput->tableName,
                        $this->changeRequestInput->columnName,
                        $this->changeRequestInput->min,
                        $this->changeRequestInput->max
                    );
                    break;
                default:
                    # code...
                    break;
            }

        // generate instance     
        $numRows = $dbTargetConnection->getNumRows($this->changeRequestInput->tableName);
        $randomData  = RandomContext::getRandomData($numRows, $this->changeRequestInput->dataType,
            [
                'length' => $this->changeRequestInput->length,
                'precision' => $this->changeRequestInput->precision,
                'scale' => $this->changeRequestInput->scale,
                'min' => $this->changeRequestInput->scale,
                'max' => $this->changeRequestInput->max
            ], 
            $this->isUnique()
        );

        $pkColumns = $this->database->getTableByName($this->changeRequestInput->tableName)->getPK()->getColumns();

        // [ [pkCol1 => value , pkCol2 => value] , [pkCol1 => value , pkCol2 => value]  ]
        $oldValues = $dbTargetConnection->getDistinctValues($this->changeRequestInput->tableName, $pkColumns);

        $dbTargetConnection->updateInstance(
            $this->changeRequestInput->tableName,
            $this->changeRequestInput->columnName,
            $oldValues,
            $randomData,
            $this->changeRequestInput->default
        );
        
    }

}