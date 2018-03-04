<?php

namespace App\Library\State;

use App\ConstraintColumn;
use App\DatabaseSchemaColumn;
use App\DatabaseSchemaConstraint;
use App\DatabaseSchemaTable;
use App\Library\Builder\DatabaseBuilder;
use App\Library\CustomModel\DBTargetConnection;
use App\Library\State\ChangeAnalysis;
use App\Library\State\StateInterface;
use DB;
use App\Library\Random\RandomContext;

class AnalyzeImpactDBState implements StateInterface
{
    private $dbTargetConnection = null;
    private $dbTarget = null;
    private $message = null;
    private $impact;

    public function process(ChangeAnalysis $changeAnalysis): bool
    {
        $requestInfo = $changeAnalysis->getRequest();
        $connectDbInfo = $requestInfo['connectDbInfo'];
        $this->dbTargetConnection = DBTargetConnection::getInstance(
            $connectDbInfo["type"],
            $connectDbInfo["hostName"],
            $connectDbInfo["dbName"],
            $connectDbInfo["username"],
            $connectDbInfo["password"]
        );
        if (!$this->dbTargetConnection->connect()) {
            $changeAnalysis->setMessage("Cannot Connect to Target Database");
            $changeAnalysis->setStatusCode(303);
            return false;
        } else {
            $this->getDbSchema();
            if ($this->saveDbSchema($changeAnalysis->getProjectId())) {
                $this->analysis($requestInfo['functionalRequirements'], $requestInfo['changeRequest']);
            } else {
                $changeAnalysis->setMessage($this->message);
                $changeAnalysis->setStatusCode(303);
                return false;
            }
        }

        $changeAnalysis->setMessage("Connect to Target Database success");
        $changeAnalysis->setStatusCode(201);
        return true;
    }

    private function getDbSchema(): void
    {
        $databaseBuilder = new DatabaseBuilder($this->dbTargetConnection);
        $databaseBuilder->setUpTablesAndColumns();
        $this->dbTarget = $databaseBuilder->getDatabase();
    }

    private function saveDbSchema(int $projectId): bool
    {
        DB::beginTransaction();
        $tempId = [];
        try {
            foreach ($this->dbTarget->getAllTables() as $table) {
                $dbSchemaTable = new DatabaseSchemaTable;
                $dbSchemaTable->projectId = $projectId;
                $dbSchemaTable->name = $table->getName();
                $dbSchemaTable->save();

                $tempId[$table->getName()] = [];

                foreach ($table->getAllColumns() as $column) {
                    $dbSchemaColumn = new DatabaseSchemaColumn;
                    $dbSchemaColumn->tableId = $dbSchemaTable->id;
                    $dbSchemaColumn->name = $column->getName();

                    $datatype = $column->getDatatype();
                    $dbSchemaColumn->dataType = $datatype->getType();
                    $dbSchemaColumn->length = $datatype->getLength();
                    $dbSchemaColumn->precision = $datatype->getPrecision();
                    $dbSchemaColumn->scale = $datatype->getScale();

                    $dbSchemaColumn->nullable = $column->isNullable() ? 1 : 0;
                    $dbSchemaColumn->default = $column->getDefault();
                    $dbSchemaColumn->save();

                    $tempId[$table->getName()][$column->getName()] = $dbSchemaColumn->id;

                }

            }

            foreach ($this->dbTarget->getAllTables() as $table) {
                $pk = $table->getPK();

                $dbSchemaConstraint = new DatabaseSchemaConstraint;
                $dbSchemaConstraint->name = $pk->getName();
                $dbSchemaConstraint->type = 'PK';
                $dbSchemaConstraint->save();

                foreach ($pk->getColumns() as $column) {
                    $constraintColumn = new ConstraintColumn;
                    $constraintColumn->constraintId = $dbSchemaConstraint->id;
                    $constraintColumn->columnId = $tempId[$table->getName()][$column];
                    $constraintColumn->save();
                }

                $fks = $table->getAllFK();

                foreach ($fks as $fk) {
                    $dbSchemaConstraint = new DatabaseSchemaConstraint;
                    $dbSchemaConstraint->name = $fk->getName();
                    $dbSchemaConstraint->type = 'FK';
                    $dbSchemaConstraint->save();

                    foreach ($fk->getColumns() as $column) {
                        $constraintColumn = new ConstraintColumn;
                        $constraintColumn->constraintId = $dbSchemaConstraint->id;
                        $constraintColumn->columnId = $tempId[$table->getName()][$column['primary']['columnName']];
                        $constraintColumn->columnRefId = $tempId[$column['reference']['tableName']][$column['reference']['columnName']];
                        $constraintColumn->save();
                    }
                }

                $checks = $table->getAllCheckConstraint();

                foreach ($checks as $check) {
                    $detail = $check->getDetail();
                    $dbSchemaConstraint = new DatabaseSchemaConstraint;
                    $dbSchemaConstraint->name = $check->getName();
                    $dbSchemaConstraint->type = 'CK';
                    $dbSchemaConstraint->rawCondition = $detail['definition'];
                    $dbSchemaConstraint->save();

                    foreach ($check->getColumns() as $column) {
                        $constraintColumn = new ConstraintColumn;
                        $constraintColumn->constraintId = $dbSchemaConstraint->id;
                        $constraintColumn->columnId = $tempId[$table->getName()][$column];
                        $constraintColumn->min = array_key_exists($column, $detail['min']) ? $detail['min'][$column]['value'] : null;
                        $constraintColumn->max = array_key_exists($column, $detail['max']) ? $detail['max'][$column]['value'] : null;
                        $constraintColumn->save();
                    }
                }

                $uniques = $table->getAllUniqueConstraint();

                foreach ($uniques as $unique) {
                    $dbSchemaConstraint = new DatabaseSchemaConstraint;
                    $dbSchemaConstraint->name = $unique->getName();
                    $dbSchemaConstraint->type = 'UN';
                    $dbSchemaConstraint->save();

                    foreach ($unique->getColumns() as $column) {
                        $constraintColumn = new ConstraintColumn;
                        $constraintColumn->constraintId = $dbSchemaConstraint->id;
                        $constraintColumn->columnId = $tempId[$table->getName()][$column];
                        $constraintColumn->save();
                    }
                }

            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            $this->message = $e->getMessage();
            return false;
        }
        return true;
    }

    private function analysis(array $functionalRequirements, array $changeRequest): void
    {
        $this->impact = array();
        $changeFunctionRequirement = $this->findFrByNo($changeRequest['functionalRequirementNo'], $functionalRequirements);

        foreach ($changeRequest['inputs'] as $changeInput) {
            $this->impact[$changeInput['name']] = [];
            $frInput = $this->findInputByName($changeInput['name'], $changeFunctionRequirement);
            if ($changeInput['changeType'] == "edit") {
                
                $this->impact[$changeInput['name']]['mainDbSchemaImpact'] = ['tableName' => $frInput['tableName'], 'columnName' => $frInput['columnName']];
                $this->impact[$changeInput['name']]['instanceImpact'] = $this->analysisEditing($changeInput, $frInput);
                $this->updateDbTartgetEdit($changeInput, $this->impact[$changeInput['name']]);
                
            } elseif ($changeInput['changeType'] == "add") {
                
                $this->impact[$changeInput['name']]['mainDbSchemaImpact'] = ['tableName' => $changeInput['tableName'], 'columnName' => $changeInput['columnName']];
                $this->impact[$changeInput['name']]['instanceImpact'] = $this->analysisAdding($changeInput, $frInput);
                
            } else {
               
                $this->impact[$changeInput['name']]['mainDbSchemaImpact'] = ['tableName' => $frInput['tableName'], 'columnName' => $frInput['columnName']];
                $this->impact[$changeInput['name']]['instanceImpact'] = $this->analysisDeleting($changeInput, $frInput);
                
            }
            


        }

    }

    private function updateDbTartgetEdit(array $newSchemaInfo, array $impactResult): void {
        $mainDbSchemaImpact = $impactResult['mainDbSchemaImpact'];
        $ckDrops = $this->findCheckConstraintRelated($mainDbSchemaImpact['tableName'],$mainDbSchemaImpact['columnName']);

        foreach ($ckDrops as $check) {
            $this->dbTargetConnection->dropConstraint($mainDbSchemaImpact['tableName'],$check->getName());
        }

        $uniqueDrops = $this->findUniqueConstraintRelated($mainDbSchemaImpact['tableName'],$mainDbSchemaImpact['columnName']);

        foreach ($uniqueDrops as $unique) {
            $this->dbTargetConnection->dropConstraint($mainDbSchemaImpact['tableName'],$unique->getName());
        }

        if($impactResult['instanceImpact']) {
            $distinctValues = $this->dbTargetConnection->getDistinctValues($mainDbSchemaImpact['tableName'],$mainDbSchemaImpact['columnName']); 
            $this->dbTargetConnection->addColumn($mainDbSchemaImpact['tableName'],$mainDbSchemaImpact['columnName']."_temp",$newSchemaInfo);
            $random = new RandomContext(\strtolower($newSchemaInfo['dataType']));
            $random = $random->random(\count($distinctValues), $newSchemaInfo ,$newSchemaInfo['unique']);
            $randomData = $random->getRandomData();
            $this->dbTargetConnection->updateInstance($mainDbSchemaImpact['tableName'],$mainDbSchemaImpact['columnName'],$distinctValues,$randomData);
            $this->dbTargetConnection->dropColumn($mainDbSchemaImpact['tableName'],$mainDbSchemaImpact['columnName']);
            $this->dbTargetConnection->updateColumnName($mainDbSchemaImpact['tableName'],$mainDbSchemaImpact['columnName']."_temp",$mainDbSchemaImpact['columnName']);
        }
        else {
            $this->dbTargetConnection->updateColumn($mainDbSchemaImpact['tableName'],$mainDbSchemaImpact['columnName'],$newSchemaInfo);
        }
        if($newSchemaInfo['unique'] == true) {
            //$this->dbTargetConnection->addUniqueConstraint($mainDbSchemaImpact['tableName'],$mainDbSchemaImpact['columnName']);
            foreach($uniqueDrops as $unique) {
                $columns = $unique->getAllColumns();
                $this->dbTargetConnection->addUniqueConstraint($mainDbSchemaImpact['tableName'],implode(",",$columns));
            }
        }
        if($newSchemaInfo['min'] != null || $newSchemaInfo['max'] != null) {
            $this->dbTargetConnection->addCheckConstraint($mainDbSchemaImpact['tableName'],$mainDbSchemaImpact['columnName'],$newSchemaInfo['min'],$newSchemaInfo['max']);
        }

    }

    private function findUniqueConstraintRelated(string $tableName, string $columnName) : array {
        $uniqueConstraints = $this->dbTarget->getTableByName($tableName)->getAllUniqueConstraint();
        $arrayUniqueRelated = [];
        foreach ($uniqueConstraints as $uniqueConstraint) {
            foreach ($uniqueConstraint->getColumns() as $column) {
                if($column == $columnName) {
                    $arrayUniqueRelated[] = $uniqueConstraint;
                    break;
                }
            }
        }
        return $arrayUniqueRelated;
    }


    private function findCheckConstraintRelated(string $tableName, string $columnName) : array {
        $checkConstraints = $this->dbTarget->getTableByName($tableName)->getAllCheckConstraint();
        $arrayCheckRelated = [];
        foreach ($checkConstraints as $checkConstraint) {
            foreach ($checkConstraint->getColumns() as $column) {
                if($column == $columnName) {
                    $arrayCheckRelated[] = $checkConstraint;
                    break;
                }
            }
        }
        return $arrayCheckRelated;
    }

    private function findFrByNo(string $no, array $functionalRequirements): array
    {
        foreach ($functionalRequirements as $functionalRequirement) {
            if ($functionalRequirement['no'] == $changeRequest['functionalRequirementNo']) {
                return $functionalRequirement;
                break;
            }
        }
        return [];
    }

    private function findInputByName(string $name, array $functionalRequirementInputs): array
    {
        foreach ($functionalRequirementInputs as $input) {
            if ($input['name'] == $name) {
                return $input;
            }
        }
        return [];
    }

    private function analysisEditing(array $changeInput, array $frInput): bool
    {
        $dbColumn = $this->dbTarget->getTableByName($frInput['tableName'])->getColumnByName($frInput['columnName']);
        if ($changeInput['dataType'] !== null) {
           if($this->findInstanceImpactByDataType($changeInput['dataType'],$dbColumn->getDataType()->getType()) ) {
               return true;
           }
        }
        if ($changeInput['length'] !== null) {
            $dbColumnLength = $dbColumn->getDataType()->getLength();
            if($dbColumnLength != null) {
                if($this->findInstanceImpactByLength($changeInput['length'],$dbColumnLength)) {
                    return true;
                }
            }
        }
        if ($changeInput['scale'] !== null) {
            $dbColumnScale = $dbColumn->getDataType()->getScale();
            if($dbColumnScale != null) {
                if($this->findInstanceImpactByScale($changeInput['scale'],$dbColumnScale)) {
                    return true;
                }
            }
        }
        if ($changeInput['unique'] !== null) {
            if($this->findInstanceImpactByUnique($changeInput['unique'], $this->isColumnUnique($frInput['tableName'],$frInput['columnName']))) {
                return true;
            }
        }
        if ($changeInput['nullable'] !== null) {
            $dbColumnNullable = $dbColumn->isNullable();
            if($this->findInstanceImpactByNullable($changeInput['nullable'],$dbColumnNullable)) {
                return true;
            }
        }
        if ($changeInput['min'] !== null) {
            $dbColumnMin = $this->getMin($frInput['tableName'],$frInput['columnName']);
            if($this->findInstanceImpactByMin($changeInput['min'],$dbColumnMin)) {
                return true;
            }
        }
        if ($changeInput['max'] !== null) {
            $dbColumnMax = $this->getMax($frInput['tableName'],$frInput['columnName']);
            if($this-findInstanceImpactByMax($changeInput['max'],$dbColumnMax)) {
                return true;
            }
        }
    }

    private function analysisAdding(array $changeInput, array $frInput): bool
    {
        if(\is_null($changeInput['tableName']) || \is_null($changeInput['columnName']) ) {
            return false;
        }
        $tables  = $this->dbTarget->getAllTables();
            if(\array_key_exists($changeInput['tableName'],$tables) ) {
                $columns = $tables->getAllColumns();
                if(\array_key_exists($changeInput['columnName'],$columns)) {
                    return true;
                }
            }
            else {
                return false;
            }
    }

    private function analysisDeleting(array $changeInput, array $frInput): bool
    {
        if($this->isPK($frInput['tableName'],$frInput['columnName'])) {
            return false;
        }
        return true;
    }

    private function isPK(string $tableName,string $columnName): bool {
        $table = $this->dbTarget->getTableByName($tableName);
        $pk = $table->getPK();
        return \in_array($columnName,$pk->getColumns());
    }

    private function findInstanceImpactByDataType(string $changeDataType, string $dbColumnDataType): bool {

        $char = ['varchar', 'char'];
        $unicodeChar = ['nvarchar', 'nchar'];
        if(array_search($changeDataType,$char) && array_search($dbColumnDataType,$char) ){
            return false;
        }
        if(array_search($changeDataType,$unicodeChar) && array_search($dbColumnDataType,$unicodeChar)) {
            return false;
        }
        if($changeDataType == $dbColumnDataType) {
            return false;
        }

        return true;

    }

    private function findInstanceImpactByLength(int $changeLength, int $dbColumnLength): bool {
        return $changeLength >= $dbColumnLength ? false : true;
    }

    private function findInstanceImpactByScale(int $changeScale, int $dbColumnScale): bool {
        return $changeScale >= $dbColumnScale ? false : true;
    }

    private function findInstanceImpactByNullable(bool $changeNullable, bool $dbColumnNullable): bool {
        return ($changeNullable === false && $dbColumnNullable === true) ? true : false;
    }

    private function findInstanceImpactByUnique(bool $changeUnique, bool $dbColumnUnique): bool{
        return ($changeUnique === true && $dbColumnUnique === false) ? true : false;
    }

    private function isColumnUnique(string $tableName, string $columnName): bool {
        $uniqueConstraints = $this->dbTarget->getTableByName($tableName)->getAllUniqueConstraint();
        foreach($uniqueConstraints as $uniqueConstraint){
            foreach ($uniqueConstraint->getColumns() as $column) {
                if($column == $columnName) {
                    return true;
                }
            }
        }
        return false;
    }

    private function getMin(string $tableName, string $columnName) {
        $checkConstraints = $this->dbTarget->getTableByName($tableName)->getAllCheckConstraint();
        foreach ($checkConstraints as $checkConstraint) {
            foreach ($checkConstraint->getColumns() as $column) {
                if($column == $columnName) {
                    $minAllColumn = $checkConstraint->getDetail()['min'];
                    if(\array_key_exists($columnName,$minAllColumn)) {
                        return $minAllColumn[$columnName];
                    }
                }
            }
        }
        return null;
    }

    private function findInstanceImpactByMin($changeMin, $columnMin): bool {
        if($columnMin == null) return true;
        return ($changeMin > $columnMin) ? true : false;
    }

    private function getMax(string $tableName, string $columnName) {
        $checkConstraints = $this->dbTarget->getTableByName($tableName)->getAllCheckConstraint();
        foreach ($checkConstraints as $checkConstraint) {
            foreach ($checkConstraint->getColumns() as $column) {
                if($column == $columnName) {
                    $minAllColumn = $checkConstraint->getDetail()['max'];
                    if(\array_key_exists($columnName,$minAllColumn)) {
                        return $minAllColumn[$columnName];
                    }
                }
            }
        }
        return null;
    }

    private function findInstanceImpactByMax($changeMax, $columnMax): bool {
        if($columnMax == null) return true;
        return ($changeMax < $columnMax) ? true : false;
    }

}
