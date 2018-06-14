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

    }

    public function analyze(): array
    {
        $result = [];

            $tableName = $this->changeRequestInput->tableName;
            $columnName = $this->changeRequestInput->columnName;
            $table = $this->database->getTableByName($tableName);
           
                $newSchema = array_filter($this->changeRequestInput->toArray(), function ($val) {
                    return $val !== null;
                });

                unset($newSchema['id']);
                unset($newSchema['crId']);
                
                $numRows = $this->dbTargetConnection->getNumRows($tableName);
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
                    strcasecmp($this->changeRequestInput->unique, 'Y') == 0
                );
                
                $result[$tableName] = [];
                $result[$tableName][$columnName] = [
                    'changeType' => 'add',
                    'old' => [],
                    'new' => $newSchema,
                    'isPK' => false,
                    'instance' => [
                        'pkRecords' => $this->dbTargetConnection->getInstanceByTableName($tableName, $table->getPK()->getColumns()),
                        'newValues' => $randomData,
                        'oldValues' => []
                    ]
                ];

                $result = [
                    'tableList' => $result,
                    'cckDelete' => [],
                    'fkDelete' => []
                ];

                return $result;
        
    }

    public function modify(): bool
    {
        if (count($this->schemaImpactResult) == 0) {
            return false;
        }

        $this->dbTargetConnection->disableConstraint();
        $this->dbTargetConnection->addColumn($this->changeRequestInput->toArray());

        $default = $this->changeRequestInput->default == '#NULL' ? null : $this->changeRequestInput->default;

        $tableName = $this->changeRequestInput->tableName;
        $columnName = $this->changeRequestInput->columnName;

        $this->dbTargetConnection->updateInstance(
            $this->changeRequestInput->tableName,
            $this->changeRequestInput->columnName,
            $this->instanceImpactResult[$tableName]['columnList'][$columnName]['oldInstance'],
            $this->instanceImpactResult[$tableName]['columnList'][$columnName]['newInstance'],
            $default
        );

        $this->dbTargetConnection->updateColumn($this->changeRequestInput->toArray());
        
        if (strcasecmp($this->changeRequestInput->unique, 'Y') == 0 ) {
            $this->dbTargetConnection->addUniqueConstraint($tableName, $columnName);
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
