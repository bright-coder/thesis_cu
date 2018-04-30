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
        

        if ($this->database->isLinked($this->functinoalRequirementInput->tableName, $this->functinoalRequirementInput->columnName)) {
            $this->findImpactLinkedColumn();
        } else {
            $this->findImpactNormalColumn();
        }
        return false;
    }

    private function findImpactLinkedColumn(): void
    {
        $table = $this->database->getTableByName($this->functinoalRequirementInput->tableName);
        $column = $table->getColumnByName($this->functionalRequirementInput->columnName);

        // If this column is not Primary column ;
        if ($table->isFK($column->getName())) {
            
            if ($this->changeRequestInput->unique != null) {
                if (\strcasecmp($this->changeRequestInput->unique, 'N') == 0) {
                    $duplicateInstance = $this->dbTargetConnection->getDuplicateInstance($table->getName(), [$column->getName()]);
                    if (count($duplicateInstance > 0)) {
                        // cannot modify impact; Referential Integrity;

                        return;
                    }
                }
            }
            if ($this->changeRequestInput->nullable != null) {
                if (\strcasecmp($this->changeRequestInput->nullable, 'N') == 0) {
                    $nullInstance =  $this->dbTargetConnection->getInstanceByTableName($table->getName(), "{$column->getName()} IS NULL");
                    if (count($nullInstance) > 0) {
                        // cannot modify impact; Referential Integrity;
                        
                        return;
                    }
                }
            }

            $refSchema = [
                'dataType' => $column->getDataType()->getType(),
                'length' => $column->getDataType()->getLength(),
                'precision' => $column->getDataType()->getPrecision(),
                'scale' => $column->getDataType()->getScale(),
                'default' => $column->getDefault(),
                'nullable' => $column->isNullable(),
                'unique' => $table->isUnique($column->getName()),
                'min' => $table->getMin($column->getName()),
                'max' => $table->getMax($column->getName())
            ];

            $newSchema = array_filter($this->changeRequestInput->toArray(), function ($val) {
                return $val !== null;
            });
    
            $this->schemaImpactResult[0] = [
                'tableName' => $table->getName(),
                'columnName' => $column->getName(),
                'oldSchema' => $refSchema,
                'newSchema' => count($newSchema) > 0 ? $newSchema : null
            ];
            
        }

        // use Primary Column to find impacted
        // This Impact will affected many table;
        $primaryColumnNode = $this->findPrimaryColumnNode($table->getName(), $column->getName());
        $primaryTable = $this->database->getTableByName($primaryColumnNode->getTableName());
        $primaryColumn = $primaryTable->getColumnByName($primaryColumnNode->getColumnName());

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

    private function findImpactNormalColumn(): void
    {
        $table = $this->database->getTableByName($this->functinoalRequirementInput->tableName);
        //dd($table);
        $column = $table->getColumnByName($this->functinoalRequirementInput->columnName);
        
        //set refSchema to oldSchema
        $refSchema = [
            'dataType' => $column->getDataType()->getType(),
            'length' => $column->getDataType()->getLength(),
            'precision' => $column->getDataType()->getPrecision(),
            'scale' => $column->getDataType()->getScale(),
            'default' => $column->getDefault(),
            'nullable' => $column->isNullable(),
            'unique' => $table->isUnique($column->getName()),
            'min' => $table->getMin($column->getName()),
            'max' => $table->getMax($column->getName())
        ];

        $newSchema = array_filter($this->changeRequestInput->toArray(), function ($val) {
            return $val !== null;
        });

        $this->schemaImpactResult[0] = [
            'tableName' => $table->getName(),
            'columnName' => $column->getName(),
            'oldSchema' => $refSchema,
            'newSchema' => count($newSchema) > 0 ? $newSchema : null
        ];

        $dataTypeRef = $refSchema['dataType'];

        $this->instanceImpactResult[0] = array();
        if ($this->changeRequestInput->dataType != null) {
            if ($this->findInstanceImpactByDataType($this->changeRequestInput->dataType, $refSchema['dataType'])) {
                $instance = $this->dbTargetConnection->getInstanceByTableName($this->functinoalRequirementInput->tableName);
                $this->instanceImpactResult[0] = $instance;
                //return false;
            }
            $refSchema['dataType'] = $this->changeRequestInput->dataType;
            $dataTypeRef = $this->changeRequestInput->dataType;
        }

        if (DataType::isStringType($dataTypeRef)) {
            if ($this->changeRequestInput->length != null) {
                if ($this->findInstanceImpactByLength($this->changeRequestInput->length, $refSchema['length'])) {
                    $instance = $this->dbTargetConnection->getInstanceByTableName($table->getName(), "LEN({$column->getName()}) > {$this->changeRequestInput->length}");
                    if (count($instance) > 0) {
                        $this->instanceImpactResult[0] = array_merge($this->instanceImpactResult[0], $instance);
                    }
                }
                $refSchema['length'] = $this->changeRequestInput->length;
            }
        } elseif (DataType::isNumericType($dataTypeRef)) {
            if ($this->changeRequestInput->min != null && $this->changeRequestInput->min != '#NULL') {
                if ($this->findInstanceImpactByMin($this->changeRequestInput->min, $refSchema['min'])) {
                    $instance = $this->dbTargetConnection->getInstanceByTableName($table->getName(), "{$column->getName()} < {$this->changeRequestInput->min}");
                    if (count($instance) > 0) {
                        $this->instanceImpactResult[0] = array_merge($this->instanceImpactResult[0], $instance);
                    }
                }
            }
            if ($this->changeRequestInput->max != null && $this->changeRequestInput->max != '#NULL') {
                if ($this->findInstanceImpactByMax($this->changeRequestInput->max, $refSchema['max'])) {
                    $instance = $this->dbTargetConnection->getInstanceByTableName($table->getName(), "{$column->getName()} > {$this->changeRequestInput->max}");
                    if (count($instance) > 0) {
                        $this->instanceImpactResult[0] = array_merge($this->instanceImpactResult[0], $instance);
                    }
                }
            }

            if ($this->changeRequestInput->min == '#NULL') {
                $refSchema['min'] = null;
            }

            if ($this->changeRequestInput->max == '#NULL') {
                $refSchema['max'] = null;
            }
        } elseif (DataType::isFloatType($dataTypeRef)) {
            if ($this->changeRequestInput->precision != null) {
                if ($this->findInstanceImpactByPrecision($this->changeRequestInput->precision, $refSchema['precision'])) {
                    $instance = $this->dbTargetConnection->getInstanceByTableName($table->getName(), "LEN({$column->getName()})-1 > {$this->changeRequestInput->precision}");
                    if (count($instance) > 0) {
                        $this->instanceImpactResult[0] = array_merge($this->instanceImpactResult[0], $instance);
                    }
                }
                $refSchema['precision'] = $this->changeRequestInput->precision;
            }
            if (\strtolower($dataType) == 'decimal') {
                if ($this->changeRequestInput->scale != null) {
                    if ($this->findInstanceImpactByScale($this->changeRequestInput->scale, $refSchema['scale'])) {
                        $instance = $this->dbTargetConnection->getInstanceByTableName($table-getName(), "LEN(SUBSTRING({$column->getName()},CHARINDEX('.', {$column->getName()})+1, 4000)) > {$this->changeRequestInput->scale}");
                        if (count($instance) > 0) {
                            $this->instanceImpactResult[0] = array_merge($this->instanceImpactResult[0], $instance);
                        }
                    }
                }
                $refSchema['scale'] = $this->changeRequestInput->scale;
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
                $instance = $this->dbTargetConnection->getInstanceByTableName($table->getName(), "{$column->getName()} IS NULL");
                if (count($instance) > 0) {
                    $this->instanceImpactResult[0] = array_merge($this->instanceImpactResult[0], $instance);
                }
            }
            $refSchema['nullable'] = \strcasecmp($this->changeRequestInput->nullable, 'N') == 0 ? false : true;
        }
        
        if ($this->changeRequestInput->unqiue != null) {
            if ($this->findInstanceImpactByUnique(\strcasecmp($this->changeRequestInput->unique, 'N') == 0 ? false : true, $refSchema['unique'])) {
                $instance = $this->dbTargetConnection->getDuplicateInstance($table->getName(), $column->getName());
                if (count($instance) > 0) {
                    $this->instanceImpactResult[0] = array_merge($this->instanceImpactResult[0], $instance);
                }
            }
            $refSchema['unique'] = \strcasecmp($this->changeRequestInput->unique, 'N') == 0 ? false : true;
        }
        
        
        if (count($this->instanceImpactResult[0]) > 0) {
            $numRows = $this->dbTargetConnection->getNumRows($table->getName());
            $randomData  = RandomContext::getRandomData(
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

            $this->instanceImpactResult[0] = array_unique($this->instanceImpactResult[0], SORT_REGULAR);
            
            if (count($randomData) >= count($this->instanceImpactResult[0])) {
                $randomData = \array_splice($randomData, 0, count($this->instanceImpactResult[0]));
            }
            $this->instanceImpactResult[0] = [
                'oldInstance' =>  $this->instanceImpactResult[0],
                'newInstance' => $randomData
            ];
            dd($this->instanceImpactResult[0]);
        //$pkColumns = $this->database->getTableByName($this->changeRequestInput->tableName)->getPK()->getColumns();
        } else {
            $this->instanceImpactResult[0] = null;
        }
    }

    public function modify(): bool
    {
        //$dbTargetConnection->addColumn($changeRequestInput);
        foreach ($this->schemaImpactResult as $index => $scResult) {
            $this->dbTargetConnection->disableConstraint();
            $this->dbTargetConnection->updateColumn($scResult);

            $default = $scResult['oldSchema']['default'];
            if (\array_key_exists('default', $scResult['newSchema'])) {
                $default = $scResult['newSchema']['default'];
                if ($default == '#NULL') {
                    $default = null;
                }
            }

            $nullable = $scResult['oldSchema']['nullable'];
            if (\array_key_exists('nullable', $scResult['newSchema'])) {
                $nullable = $scResult['newSchema']['nullable'];
            }

            if ($default === null && $nullable === false) {
                $default = '';
            }
            

            if ($this->instanceImpactResult[$index] != null) {
                $this->dbTargetConnection->updateInstance(
                    $scResult['tableName'],
                    $scResult['columnName'],
                    $this->instanceImpactResult[$index]['oldInstance'],
                    $this->instanceImpactResult[$index]['newInstance'],
                    $default
                );
            }

            if (\array_key_exists('unique', $scResult['newSchema'])) {
                if ($scResult['newSchema']['unique'] === false) {
                    $uniqueConstraintList = $this->findUniqueConstraintRelated($scResult['tableName'], $scResult['column']);
                    if (count($uniqueConstraintList) > 0) {
                        foreach ($uniqueConstraintList as $uniqueConstraint) {
                            $this->dbTargetConnection->dropConstraint($scResult['tableName'], $uniqueConstraint->getName());
                        }
                    }
                } elseif ($scResult['newSchema']['unique'] === true && $scResult['oldSchema']['unique'] === false) {
                    $this->dbTargetConnection->addUniqueConstraint($scResult['tableName'], $scResult['column']);
                }
            }

            $dataTypeRef = $scResult['oldSchema']['dataType'];
            if (\array_key_exists('dataType', $scResult['newSchema'])) {
                $dataTypeRef = $scResult['newSchema']['dataType'];
            }

            if (DataType::isNumericType($dataTypeRef)) {
                $checkConstraintList = $this->findCheckConstraintRelated($scResult['tableName'], $scResult['column']);
                if (count($checkConstraintList) > 0) {
                    foreach ($checkConstraintList as $checkConstraint) {
                        $this->dbTargetConnection->dropConstraint($scResult['tableName'], $checkConstraint->getName());
                    }
                }
                $min = $scResult['oldSchema']['min'];
                if (array_key_exists('min', $scResult['newSchema'])) {
                    $min = $scResult['newSchema']['min'] == '#NULL' ? null : $scResult['newSchema']['min'];
                }

                $max = $scResult['oldSchema']['max'];
                if (array_key_exists('max', $scResult['newSchema'])) {
                    $max = $scResult['newSchema']['max'] == '#NULL' ? null : $scResult['newSchema']['max'];
                }

                $this->dbTargetConnection->addCheckConstraint($scResult['tableName'], $scResult['column'], $min, $max);
            } else {
                if (\array_key_exists('dataType', $scResult['newSchema'])) {
                    if (! DataType::isNumericType($scResult['newSchema']['dataType'])) {
                        $checkConstraintList = $this->findCheckConstraintRelated($scResult['tableName'], $scResult['column']);
                        if (count($checkConstraintList) > 0) {
                            foreach ($checkConstraintList as $checkConstraint) {
                                $this->dbTargetConnection->dropConstraint($scResult['tableName'], $checkConstraint->getName());
                            }
                        }
                    }
                }
            }
        }

        $this->dbTargetConnection->enableConstraint();

        return true;
    }

    private function findFunctionalRequirementById(string $id) : FunctionalRequirement
    {
        return FunctionalRequirement::where('id', $id)->first();
    }

    private function findFunctionalRequirementInputById(string $id) : FunctionalRequirementInput
    {
        return FunctionalRequirementInput::where('id', $id)->first();
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

    private function findUniqueConstraintRelated(string $tableName, string $columnName): array
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

    private function findCheckConstraintRelated(string $tableName, string $columnName): array
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
}
