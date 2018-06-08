<?php

namespace App\Library\State\AnalyzeDBMethod;

use App\Library\State\AnalyzeDBMethod\AbstractAnalyzeDBMethod;
use App\Model\ChangeRequestInput;
use App\Library\Database\Database;
use App\Library\CustomModel\DBTargetInterface;
use App\Library\Random\RandomContext;

class AnalyzeDBAdd extends AbstractAnalyzeDBMethod
{
    public function __construct(Database $database, ChangeRequestInput $changeRequestInput, DBTargetInterface $dbTargetConnection)
    {
        $this->database = $database;
        $this->changeRequestInput = $changeRequestInput;
        $this->dbTargetConnection = $dbTargetConnection;
        if ($changeRequestInput->frInputId !== null) {
            $this->functionalRequirementInput = $this->findFunctionalRequirementInputById($changeRequestInput->frInputId);
        }
    }

    public function analyze(): bool
    {
        if ($this->functionalRequirementInput === null) {
            $table = $this->database->getTableByName($this->changeRequestInput->tableName);

            // if not have column in table
            if (!$table->getColumnbyName($this->changeRequestInput->columnName)) {
                $newSchema = array_filter($this->changeRequestInput->toArray(), function ($val) {
                    return $val !== null;
                });

                unset($newSchema['id']);
                unset($newSchema['changeRequestId']);
                unset($newSchema['functionalRequirmenInputId']);
                unset($newSchema['tableName']);
                unset($newSchema['columnName']);
                
                $this->schemaImpactResult[0] = [
                    'tableName' => $this->changeRequestInput->tableName,
                    'columnName' => $this->changeRequestInput->columnName,
                    'changeType' => 'add',
                    'oldSchema' => null,
                    'newSchema' => $newSchema
                ];


                $numRows = $this->dbTargetConnection->getNumRows($this->changeRequestInput->tableName);
                $randomData  = RandomContext::getRandomData(
                    $numRows,
                    $this->changeRequestInput->dataType,
                    [
                        'length' => $this->changeRequestInput->length,
                        'precision' => $this->changeRequestInput->precision,
                        'scale' => $this->changeRequestInput->scale,
                        'min' => $this->changeRequestInput->min === null ? 1 : $this->changeRequestInput->min,
                        'max' => $this->changeRequestInput->max === null ? 1000000 : $this->changeRequestInput->max
                    ],
                    strcasecmp($this->changeRequestInput->unique, 'N') == 0 ? false : true
                );
                
                
                $this->instanceImpactResult[0] = [
                    'oldInstance' => $this->dbTargetConnection->getInstanceByTableName($this->changeRequestInput->tableName),
                    'newInstance' => $randomData
                ];

                return true;
            }
            return false;
        }
    }

    public function modify(): bool
    {
        if (count($this->schemaImpactResult) == 0) {
            return false;
        }

        $this->dbTargetConnection->disableConstraint();
        $this->dbTargetConnection->addColumn($this->changeRequestInput->toArray());

        $default = $this->changeRequestInput->default == '#NULL' ? null : $this->changeRequestInput->default;

        $this->dbTargetConnection->updateInstance(
            $this->changeRequestInput->tableName,
            $this->changeRequestInput->columnName,
            $this->instanceImpactResult[0]['oldInstance'],
            $this->instanceImpactResult[0]['newInstance'],
            $default
        );

        $this->dbTargetConnection->updateColumn($this->changeRequestInput->toArray());
        
        if (strcasecmp($this->changeRequestInput->unique, 'N') == 0 ? false : true) {
            $this->dbTargetConnection->addUniqueConstraint($this->changeRequestInput->tableName, $this->changeRequestInput->columnName);
        }
        
        if ($this->changeRequestInput->min !== null || $this->changeRequestInput->max !== null) {
            switch ($this->changeRequestInput->dataType) {
                case 'int':
                case 'float':
                case 'decimal':
                    $this->dbTargetConnection->addCheckConstraint(
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
        }
        $this->dbTargetConnection->enableConstraint();
        return true;
    }
}
