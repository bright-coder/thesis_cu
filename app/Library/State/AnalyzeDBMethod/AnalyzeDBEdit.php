<?php

namespace App\Library\State\AnalyzeDBMethod;

use App\Library\State\AnalyzeDBMethod\AbstractAnalyzeDBMethod;
use App\Model\ChangeRequestInput;
use App\Library\Database\Database;
use App\Library\CustomModel\DBTargetInterface;
use App\Model\FunctionalRequirement;
use App\Model\FunctionalRequirementInput;
use App\Library\Node;
use App\Library\Datatype\DataType;
use App\Library\Random\RandomContext;

class AnalyzeDBEdit extends AbstractAnalyzeDBMethod
{
    
    /**
     * Undocumented variable
     *
     * @var FunctionalRequirement;
     */
    private $functionalRequirement = null;

    /**
     * Undocumented variable
     *
     * @var Node
     */
    private $primaryColumnNode = null;


    public function __construct(Database $database, ChangeRequestInput $changeRequestInput, DBTargetInterface $dbTargetConnection)
    {
        $this->database = $database;
        $this->changeRequestInput = $changeRequestInput;
        $this->dbTargetConnection = $dbTargetConnection;
        //$this->functionalRequirement = $this->findFunctionalRequirementById($frId);
        $this->functionalRequirementInput = $this->getFRInputById($changeRequestInput->frInputId);
    }

    public function analyze() : array
    {
        $this->schemaImpact = true;


        if ($this->database->isLinked($this->functionalRequirementInput->tableName, $this->functionalRequirementInput->columnName)) {
            return $this->findImpactLinkedColumn();
        } else {
            return $this->findImpactNormalColumn();
        }
        //return false;
    }

    private function findImpactLinkedColumn(): array
    {
        $table = $this->database->getTableByName($this->functionalRequirementInput->tableName);
        $column = $table->getColumnByName($this->functionalRequirementInput->columnName);
        $result = [];
        $cckDelete = [];

        // use Primary Column to find impacted
        // This Impact will affected many table;
        $this->primaryColumnNode = $this->findPrimaryColumnNode($table->getName(), $column->getName());

        $table = $this->database->getTableByName($this->primaryColumnNode->getTableName());
        //dd($table);
        $column = $table->getColumnByName($this->primaryColumnNode->getColumnName());
        
        //set refSchema to oldSchema
        $refSchema = [
            'dataType' => $column->getDataType()->getType(),
            'length' => $column->getDataType()->getLength(),
            'precision' => $column->getDataType()->getPrecision(),
            'scale' => $column->getDataType()->getScale(),
            'default' => $column->getDefault(),
            'nullable' => $column->isNullable(),
            'unique' => $table->isUnique($column->getName()),
            'min' => $table->getMin($column->getName())['value'],
            'max' => $table->getMax($column->getName())['value']
        ];

        $newSchema = array_filter($this->changeRequestInput->toArray(), function ($val) {
            return $val !== null;
        });
        unset($newSchema['id']);
        unset($newSchema['changeRequestId']);
        unset($newSchema['functionalRequirmenInputId']);
        unset($newSchema['tableName']);
        unset($newSchema['columnName']);

        if ($this->functionalRequirementInput->tableName != $this->primaryColumnNode->getTableName() ||
            $this->functionalRequirementInput->columnName != $this->primaryColumnNode->getColumnName()) {
            unset($newSchema['default']);
            unset($newSchema['nullable']);
            unset($newSchema['unique']);
        }

        $result[$table->getName()] = [];
        $result[$table->getName()][$column->getName()] = [
            'changeType' => 'edit',
            'old' => $refSchema,
            'new' => $newSchema,
            'isPK' => $table->isPK($column->getName()),
            'instance' => []
        ];

        $dataTypeRef = $refSchema['dataType'];
        
        $records = [];
        $columnList = array_unique(array_merge(
            $table->getPK()->getColumns(),
            [$column->getName()]
        ));
        if ($this->changeRequestInput->dataType != null) {
            if ($this->findInstanceImpactByDataType($this->changeRequestInput->dataType, $refSchema['dataType'])) {
                $instance = $this->dbTargetConnection->getInstanceByTableName($this->functionalRequirementInput->tableName, $columnList);
                $records = $instance;
                //return false;
            }
            $refSchema['dataType'] = $this->changeRequestInput->dataType;
            $dataTypeRef = $this->changeRequestInput->dataType;
        }

        if (DataType::isStringType($dataTypeRef)) {
            if ($this->changeRequestInput->length != null) {
                if ($this->findInstanceImpactByLength($this->changeRequestInput->length, $refSchema['length'])) {
                    $instance = $this->dbTargetConnection->getInstanceByTableName($table->getName(), $columnList, "LEN({$column->getName()}) > {$this->changeRequestInput->length}");
                    if (count($instance) > 0) {
                        $records = array_merge($records, $instance);
                    }
                }
                $refSchema['length'] = $this->changeRequestInput->length;
            }
        } elseif (DataType::isNumericType($dataTypeRef)) {
            if ($this->changeRequestInput->min != null && $this->changeRequestInput->min != '#NULL') {
                if ($this->findInstanceImpactByMin($this->changeRequestInput->min, $refSchema['min'])) {
                    $instance = $this->dbTargetConnection->getInstanceByTableName($table->getName(), $columnList, "{$column->getName()} < {$this->changeRequestInput->min}");
                    if (count($instance) > 0) {
                        $records = array_merge($records, $instance);
                    }
                }
                $refSchema['min'] = $this->changeRequestInput->min;
            }
            if ($this->changeRequestInput->max != null && $this->changeRequestInput->max != '#NULL') {
                if ($this->findInstanceImpactByMax($this->changeRequestInput->max, $refSchema['max'])) {
                    $instance = $this->dbTargetConnection->getInstanceByTableName($table->getName(), $columnList, "{$column->getName()} > {$this->changeRequestInput->max}");
                    if (count($instance) > 0) {
                        $records = array_merge($records, $instance);
                    }
                }

                $refSchema['max'] = $this->changeRequestInput->max;
            }

            if ($this->changeRequestInput->min == '#NULL') {
                $refSchema['min'] = null;
            }

            if ($this->changeRequestInput->max == '#NULL') {
                $refSchema['max'] = null;
            }
            if (DataType::isFloatType($dataTypeRef)) {
                if ($this->changeRequestInput->precision != null) {
                    if ($this->findInstanceImpactByPrecision($this->changeRequestInput->precision, $refSchema['precision'] ? $refSchema['precision'] : 999 )) {
                        $instance = $this->dbTargetConnection->getInstanceByTableName($table->getName(), $columnList, "LEN({$column->getName()})-1 > {$this->changeRequestInput->precision}");
                        if (count($instance) > 0) {
                            $records = array_merge($records, $instance);
                        }
                    }
                    $refSchema['precision'] = $this->changeRequestInput->precision;
                }
                if (\strtolower($dataTypeRef) == 'decimal') {
                    if ($this->changeRequestInput->scale != null) {
                        if ($this->findInstanceImpactByScale($this->changeRequestInput->scale, $refSchema['scale'] ? $refSchema['scale'] : 999 )) {
                            $instance = $this->dbTargetConnection->getInstanceByTableName($table-getName(), $columnList, "LEN(SUBSTRING({$column->getName()},CHARINDEX('.', {$column->getName()})+1, 4000)) > {$this->changeRequestInput->scale}");
                            if (count($instance) > 0) {
                                $records = array_merge($records, $instance);
                            }
                        }
                    }
                    $refSchema['scale'] = $this->changeRequestInput->scale;
                }
            }
        }

        if ($this->primaryColumnNode->getTableName() == $this->functionalRequirementInput->tableName &&
            $this->primaryColumnNode->getColumnName() == $this->functionalRequirementInput->columnName) {
            if ($this->changeRequestInput->default != null) {
                if ($this->changeRequestInput->default == '#NULL') {
                    $refSchema['default'] = null;
                } else {
                    $refSchema['default'] = $this->changeRequestInput->default;
                }
            }

            if ($this->changeRequestInput->nullable != null) {
                if ($this->findInstanceImpactByNullable(\strcasecmp($this->changeRequestInput->nullable, 'N') == 0 ? false : true, $refSchema['nullable'])) {
                    $instance = $this->dbTargetConnection->getInstanceByTableName($table->getName(), $columnList, "{$column->getName()} IS NULL");
                    if (count($instance) > 0) {
                        $records = array_merge($records, $instance);
                    }
                }
                $refSchema['nullable'] = \strcasecmp($this->changeRequestInput->nullable, 'Y') == 0;
            }
            
            if ($this->changeRequestInput->unqiue != null) {
                if ($this->findInstanceImpactByUnique(\strcasecmp($this->changeRequestInput->unique, 'Y') == 0, $refSchema['unique'])) {
                    $instance = $this->dbTargetConnection->getDuplicateInstance($table->getName(), $column->getName());
                    if (count($instance) > 0) {
                        $records = array_merge($records, $instance);
                    }
                    $uniqueRelated = $this->database->findUniqueConstraintRelated($table->getName(), $column->getName());
                    foreach ($uniqueRelated as $unique) {
                        if (count($unique->getColumns()) > 1) {
                            $cckDelete[] = [
                                'tableName' => $table->getName(),
                                'info' => $unique
                            ];
                        }
                    }
                }
                $refSchema['unique'] = \strcasecmp($this->changeRequestInput->unique, 'Y') == 0;
            }
        }

        if (count($records) > 0) {
            $records = array_unique($records, SORT_REGULAR);
            $numRows = count($records);
            $randomData = RandomContext::getRandomData(
                $numRows,
                $refSchema['dataType'],
                [
                    'length' => $refSchema['length'],
                    'precision' => $refSchema['precision'],
                    'scale' => $refSchema['scale'],
                    'min' => $refSchema['min'],
                    'max' => $refSchema['max']
                ],
                $refSchema['unique']
            );

            $oldValues = [];
            if ($table->isPK($column->getName())) {
                foreach ($records as $record) {
                    $oldValues[] = $record[$column->getName()];
                }
            } else {
                foreach ($records as $index => $record) {
                    $oldValues[] = $record[$column->getName()];
                    unset($records[$index][$column->getName()]);
                }
            }
            //dd($oldValues);
            $numRows = count(array_unique($oldValues));
            $randomData = RandomContext::getRandomData(
                $numRows,
                $refSchema['dataType'],
                [
                    'length' => $refSchema['length'],
                    'precision' => $refSchema['precision'],
                    'scale' => $refSchema['scale'],
                    'min' => $refSchema['min'],
                    'max' => $refSchema['max']
                ],
                true
            );
            $newValues = [];
            $oldUniques = array_unique($oldValues);
            foreach ($oldUniques as $i => $old) {
                $newValue = array_pop($randomData);
                foreach ($oldValues as $j => $oldValue) {
                    if ($old == $oldValue) {
                        $newValues[$j] = $newValue;
                    }
                }
            }
            ksort($newValues);
            
            $result[$table->getName()][$column->getName()]['instance'] = [
                'pkRecords' => $records,
                'newValues' => $newValues,
                'oldValues' => $oldValues
            ];
            $result[$table->getName()][$column->getName()]['isImpactAll'] = $this->dbTargetConnection->getNumRows($table->getName()) == count($newValues);
        }

        $q = $this->primaryColumnNode->getLinks();

        while ($q) {
            $node = array_shift($q);
            $table = $this->database->getTableByName($node->getTableName());
            $column = $table->getColumnByName($node->getColumnName());
            $refSchema = [
                'dataType' => $column->getDataType()->getType(),
                'length' => $column->getDataType()->getLength(),
                'precision' => $column->getDataType()->getPrecision(),
                'scale' => $column->getDataType()->getScale(),
                'default' => $column->getDefault(),
                'nullable' => $column->isNullable(),
                'unique' => $table->isUnique($column->getName()),
                'min' => $table->getMin($column->getName())['value'],
                'max' => $table->getMax($column->getName())['value'],
                'tableName' => $table->getName(),
                'columnName' => $column->getName()
            ];

            $newSchema = $result[$this->primaryColumnNode->getTableName()][$this->primaryColumnNode->getColumnName()]['new'];
            unset($newSchema['default']);
            unset($newSchema['nullable']);
            unset($newSchema['unique']);

            if ($node->getTableName() == $this->functionalRequirementInput->tableName &&
                $node->getColumnName() == $this->functionalRequirementInput->columnName) {
                // if ($this->changeRequestInput->default != null) {
                //     if ($this->changeRequestInput->default == '#NULL') {
                //         $newSchema['default'] = null;
                //     } else {
                //         $newSchema['default'] = $this->changeRequestInput->default;
                //     }
                // }
                if ($refSchema['default'] != null) {
                    $newSchema['default'] = null;
                }
        
                if ($this->changeRequestInput->nullable != null) {
                    $newSchema['nullable'] = \strcasecmp($this->changeRequestInput->nullable, 'Y') == 0;
                }
                    
                if ($this->changeRequestInput->unqiue != null) {
                    $newSchema['unique'] = \strcasecmp($this->changeRequestInput->unique, 'Y') == 0;

                    if (! $newSchema['unique']) {
                        $uniqueRelated = $this->database->findUniqueConstraintRelated($node->getTableName(), $node->getColumnName());
                        foreach ($uniqueRelated as $unique) {
                            if (count($unique->getColumns()) > 1) {
                                $cckDelete[] = [
                                    'tableName' => $node->getTableName(),
                                    'info' => $unique
                                ];
                            }
                        }
                    }
                }
            }

            if (!isset($result[$table->getName()])) {
                $result[$table->getName()] = [];
            }
            $result[$table->getName()][$column->getName()] = [
                'changeType' => 'edit',
                'old' => $refSchema,
                'new' => $newSchema,
                'isPK' => $table->isPK($column->getName()),
                'instance' => []
            ];

            //$primeInstance = $result[$this->primaryColumnNode->getTableName()][$this->primaryColumnNode->getColumnName()]['instance'];
            if (count($records) > 0) {
                $columnList = array_unique(array_merge(
                    $table->getPK()->getColumns(),
                    [$column->getName()]
                ));
                $WHERE_CAUSE = $column->getName()." IN (".implode(",", $oldValues).")";
                $instance = $this->dbTargetConnection->getInstanceByTableName($table->getName(), $columnList, $WHERE_CAUSE);
                if (count($instance) > 0) {
                    $oldValuesSec = [];
                    
                    if ($table->isPK($column->getName())) {
                        foreach ($instance as $record) {
                            $oldValuesSec[] = $record[$column->getName()];
                        }
                    } else {
                        foreach ($instance as $index => $record) {
                            $oldValuesSec[] = $record[$column->getName()];
                            unset($instance[$index][$column->getName()]);
                        }
                    }

                    $newValuesSec = [];
                    foreach ($oldValuesSec as $i => $oldSec) {
                        //dd($result[$table->getName()][$column->getName()]['instance']);
                        foreach ($result[$this->primaryColumnNode->getTableName()][$this->primaryColumnNode->getColumnName()]['instance']['oldValues'] as $j => $oldPrime) {
                            if ($oldSec == $oldPrime) {
                                $newValuesSec[$i] = $result[$this->primaryColumnNode->getTableName()][$this->primaryColumnNode->getColumnName()]['instance']['newValues'][$j];
                            }
                        }
                    }
                    ksort($newValuesSec);
                    $result[$table->getName()][$column->getName()]['instance'] = [
                        'pkRecords' => $instance,
                        'oldValues' => $oldValuesSec,
                        'newValues' => $newValuesSec,
                    ];
                    $result[$table->getName()][$column->getName()]['isImpactAll'] = $this->dbTargetConnection->getNumRows($table->getName()) == count($newValuesSec);
                }
            }

            if ($node->getLinks()) {
                $q = array_merge($q, $node->getLinks());
            }
        }
        if($result) {
            $result = [
                'tableList' => $result,
                'cckDelete' => $cckDelete,
                'fkDelete' => []
            ];
        }
        return $result;
    }

    private function findImpactNormalColumn(): array
    {
        $table = $this->database->getTableByName($this->functionalRequirementInput->tableName);
 
        $column = $table->getColumnByName($this->functionalRequirementInput->columnName);

        $cckDelete = [];
        $refSchema = [
            'dataType' => $column->getDataType()->getType(),
            'length' => $column->getDataType()->getLength(),
            'precision' => $column->getDataType()->getPrecision(),
            'scale' => $column->getDataType()->getScale(),
            'default' => $column->getDefault(),
            'nullable' => $column->isNullable(),
            'unique' => $table->isUnique($column->getName()),
            'min' => $table->getMin($column->getName())['value'],
            'max' => $table->getMax($column->getName())['value'],
            'tableName' => $table->getName(),
            'columnName' => $column->getName()
        ];

        $newSchema = array_filter($this->changeRequestInput->toArray(), function ($val) {
            return $val !== null;
        });
        $result = [];
        $result[$table->getName()] = [];
        $result[$table->getName()][$column->getName()] = [
            'changeType' => 'edit',
            'old' => $refSchema,
            'new' => $newSchema,
            'isPK' => false,
            'instance' => []
        ];

        $dataTypeRef = $refSchema['dataType'];

        $records = [];
        $columnList = array_unique(array_merge(
            $table->getPK()->getColumns(),
            [$column->getName()]
        ));
        $compositeCandidateKeyImpact = [];
        if ($this->changeRequestInput->dataType != null) {
            if ($this->findInstanceImpactByDataType($this->changeRequestInput->dataType, $refSchema['dataType'])) {
                $records = $this->dbTargetConnection->getInstanceByTableName($table->getName(), $columnList);
            
                //return false;
            }
            $refSchema['dataType'] = $this->changeRequestInput->dataType;
            $dataTypeRef = $this->changeRequestInput->dataType;
        }

        if (DataType::isStringType($dataTypeRef)) {
            if ($this->changeRequestInput->length != null) {
                if ($this->findInstanceImpactByLength($this->changeRequestInput->length, $refSchema['length'])) {
                    $instance = $this->dbTargetConnection->getInstanceByTableName($table->getName(), $columnList, "LEN({$column->getName()}) > {$this->changeRequestInput->length}");
                    if (count($instance) > 0) {
                        $records = array_merge($records, $instance);
                    }
                }
                $refSchema['length'] = $this->changeRequestInput->length;
            }
        } elseif (DataType::isNumericType($dataTypeRef)) {
            if ($this->changeRequestInput->min != null && $this->changeRequestInput->min != '#NULL') {
                if ($this->findInstanceImpactByMin($this->changeRequestInput->min, $refSchema['min'])) {
                    $instance = $this->dbTargetConnection->getInstanceByTableName($table->getName(), $columnList, "{$column->getName()} < {$this->changeRequestInput->min}");
                    if (count($instance) > 0) {
                        $records = array_merge($records, $instance);
                    }
                }
                $refSchema['min'] = $this->changeRequestInput->min;
            }
            if ($this->changeRequestInput->max != null && $this->changeRequestInput->max != '#NULL') {
                if ($this->findInstanceImpactByMax($this->changeRequestInput->max, $refSchema['max'])) {
                    $instance = $this->dbTargetConnection->getInstanceByTableName($table->getName(), $columnList, "{$column->getName()} > {$this->changeRequestInput->max}");
                    if (count($instance) > 0) {
                        $records = array_merge($records, $instance);
                    }
                }
                $refSchema['max'] = $this->changeRequestInput->max;
            }

            if ($this->changeRequestInput->min == '#NULL') {
                $refSchema['min'] = null;
            }

            if ($this->changeRequestInput->max == '#NULL') {
                $refSchema['max'] = null;
            }
            if (DataType::isFloatType($dataTypeRef)) {
                if ($this->changeRequestInput->precision != null) {
                    if ($this->findInstanceImpactByPrecision($this->changeRequestInput->precision, $refSchema['precision'] ? $refSchema['precision'] : 999)) {
                        $instance = $this->dbTargetConnection->getInstanceByTableName($table->getName(), $columnList, "LEN({$column->getName()})-1 > {$this->changeRequestInput->precision}");
                        if (count($instance) > 0) {
                            $records = array_merge($records, $instance);
                        }
                    }
                    $refSchema['precision'] = $this->changeRequestInput->precision;
                }
                if (\strtolower($dataTypeRef) == 'decimal') {
                    if ($this->changeRequestInput->scale != null) {
                        if ($this->findInstanceImpactByScale($this->changeRequestInput->scale, $refSchema['scale'])) {
                            $instance = $this->dbTargetConnection->getInstanceByTableName($table-getName(), "LEN(SUBSTRING({$column->getName()},CHARINDEX('.', {$column->getName()})+1, 4000)) > {$this->changeRequestInput->scale}");
                            if (count($instance) > 0) {
                                $records = array_merge($records, $instance);
                            }
                        }
                    }
                    $refSchema['scale'] = $this->changeRequestInput->scale;
                }
            }
        }

        if ($this->changeRequestInput->default != null) {
            if ($this->changeRequestInput->default == '#NULL') {
                $refSchema['default'] = null;
            } else {
                $refSchema['default'] = $this->changeRequestInput->default;
            }
        }

        if ($this->changeRequestInput->nullable != null) {
            if ($this->findInstanceImpactByNullable(\strcasecmp($this->changeRequestInput->nullable, 'N') == 0 ? false : true, $refSchema['nullable'])) {
                $instance = $this->dbTargetConnection->getInstanceByTableName($table->getName(), $columnList, "{$column->getName()} IS NULL");
                if (count($instance) > 0) {
                    $records = array_merge($records, $instance);
                }
            }
            $refSchema['nullable'] = \strcasecmp($this->changeRequestInput->nullable, 'N') == 0 ? false : true;
        }

        if ($this->changeRequestInput->unique != null) {
            if ($this->findInstanceImpactByUnique(\strcasecmp($this->changeRequestInput->unique, 'N') == 0 ? false : true, $refSchema['unique'])) {
                $instance = $this->dbTargetConnection->getDuplicateInstance($table->getName(), [ $column->getName() ], $table->getPK()->getColumns());
                
                if (count($instance) > 0) {
                    $records = array_merge($records, $instance);
                }
                $uniqueRelated = $this->database->findUniqueConstraintRelated($table->getName(), $column->getName());
                foreach ($uniqueRelated as $unique) {
                    if (count($unique->getColumns()) > 1) {
                        $cckDelete[] = [
                            'tableName' => $table->getName(),
                            'info' => $unique
                        ];
                    }
                }
            }
            
            $refSchema['unique'] = \strcasecmp($this->changeRequestInput->unique, 'Y') == 0 ;
        }

        if (count($records) > 0) {
            $records = array_unique($records, SORT_REGULAR);

            $oldValues = [];
            if ($table->isPK($column->getName())) {
                foreach ($records as $record) {
                    $oldValues[] = $record[$column->getName()];
                }
            } else {
                foreach ($records as $index => $record) {
                    $oldValues[] = $record[$column->getName()];
                    unset($records[$index][$column->getName()]);
                }
            }

            //dd($refSchema);
            if ($refSchema['unique']) {
                $numRows = count($records);
                $newValues = RandomContext::getRandomData(
                $numRows,
                $refSchema['dataType'],
                [
                    'length' => $refSchema['length'],
                    'precision' => $refSchema['precision'],
                    'scale' => $refSchema['scale'],
                    'min' => $refSchema['min'],
                    'max' => $refSchema['max']
                ],
                true
                );
            } else {
                $numRows = count(array_unique($oldValues));
                $randomData = RandomContext::getRandomData(
                    $numRows,
                    $refSchema['dataType'],
                    [
                        'length' => $refSchema['length'],
                        'precision' => $refSchema['precision'],
                        'scale' => $refSchema['scale'],
                        'min' => $refSchema['min'],
                        'max' => $refSchema['max']
                    ],
                    true
                );
                $newValues = [];
                $oldUniques = array_unique($oldValues);
                foreach ($oldUniques as $i => $old) {
                    $newValue = array_pop($randomData);
                    foreach ($oldValues as $j => $oldValue) {
                        if ($old == $oldValue) {
                            $newValues[$j] = $newValue;
                        }
                    }
                }
                ksort($newValues);
            }
        

            $result[$table->getName()][$column->getName()]['instance'] = [
                'pkRecords' => $records,
                'newValues' => $newValues,
                'oldValues' => $oldValues
            ];
            $result[$table->getName()][$column->getName()]['isImpactAll'] = $this->dbTargetConnection->getNumRows($table->getName()) == count($newValues);
        }
        if($result) {
            $result = [
                'tableList' => $result,
                'cckDelete' => $cckDelete,
                'fkDelete' => []
            ];
        }
        
        
        return $result;
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

    private function findInstanceImpactByMax($changeMax, $columnMax): bool
    {
        if ($columnMax == null) {
            return true;
        }

        return ($changeMax < $columnMax) ? true : false;
    }

    private function findPrimaryColumnNode(string $tableName, string $columnName) : Node
    {
        $hashFKs = $this->database->getHashFks();
        $hashFKs = $hashFKs[$tableName][$columnName];

        while ($hashFKs->getPrevious() !== null) {
            $hashFKs = $hashFKs->getPrevious();
        }
        return $hashFKs;
    }

}
