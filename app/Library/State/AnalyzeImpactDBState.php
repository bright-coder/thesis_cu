<?php

namespace App\Library\State;

use App\ConstraintColumn;
use App\DatabaseSchemaColumn;
use App\DatabaseSchemaConstraint;
use App\DatabaseSchemaTable;
use App\Library\Builder\DatabaseBuilder;
use App\Library\CustomModel\DBTargetConnection;
use App\Library\Random\RandomContext;
use App\Library\State\ChangeAnalysis;
use App\Library\State\StateInterface;
use DB;

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
            $this->analysis($requestInfo['functionalRequirements'], $requestInfo['changeRequest']);
            // if ($this->saveDbSchema($changeAnalysis->getProjectId())) {
            //     $this->analysis($requestInfo['functionalRequirements'], $requestInfo['changeRequest']);
            // } else {
            //     $changeAnalysis->setMessage($this->message);
            //     $changeAnalysis->setStatusCode(303);
            //     return false;
            // }
        }
        
        $changeAnalysis->setMessage("Analysis DbSchema success");
        $changeAnalysis->setStatusCode(200);
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
            $frInput = $this->findInputByName($changeInput['name'], $changeFunctionRequirement['inputs']);
            if ($changeInput['changeType'] == "edit") {
                $this->impact[$changeInput['name']]['dbSchemaImpact'] = ['tableName' => $frInput['tableName'], 'columnName' => $frInput['columnName']];
                $this->impact[$changeInput['name']]['instanceImpact'] = $this->analysisEditing($changeInput, $frInput);
                $this->updateDbTargetEdit($changeInput, $this->impact[$changeInput['name']], $frInput);
                
                

            } elseif ($changeInput['changeType'] == "add") {

                $this->impact[$changeInput['name']]['instanceImpact'] = $this->analysisAdding($changeInput, $frInput);
                if ($this->impact[$changeInput['name']]['instanceImpact'] == true) {
                    $this->impact[$changeInput['name']]['dbSchemaImpact'] = ['tableName' => $changeInput['tableName'], 'columnName' => $changeInput['columnName']];
                    $this->updateDbTargetAdding($changeInput);
                }

            } else {

                $this->impact[$changeInput['name']]['dbSchemaImpact'] = ['tableName' => $frInput['tableName'], 'columnName' => $frInput['columnName']];
                $this->impact[$changeInput['name']]['instanceImpact'] = $this->analysisDeleting($changeInput, $frInput, $functionalRequirements, $changeFunctionRequirement['no']);
                if ($this->impact[$changeInput['name']]['instanceImpact'] == true) {
                    $this->updateDbTargetDeleting($changeInput, $frInput);
                }

            }

        }

    }

    private function prepareBefore($newSchemaInfo, $frInput) : array {
        $schema = [];
        $schema["dataType"] = $newSchemaInfo["dataType"] == null ? $frInput["dataType"] : $newSchemaInfo["dataType"];
        $schema['length'] = $newSchemaInfo["length"] == null ? $frInput["length"] : $newSchemaInfo["length"];
        $schema['precision'] = $newSchemaInfo["precision"] == null ? $frInput["precision"] : $newSchemaInfo["precision"];
        $schema['scale'] = $newSchemaInfo["scale"] == null ? $frInput["scale"] : $newSchemaInfo["scale"];
        $schema['default'] = $newSchemaInfo["default"] == null ? $frInput["default"] : $newSchemaInfo["default"];
        $schema['unique'] = $newSchemaInfo["unique"] == null ? $frInput["unique"] : $newSchemaInfo["unique"];
        $schema['nullable'] = $newSchemaInfo["nullable"] == null ? $frInput["nullable"] : $newSchemaInfo["nullable"];
        $schema['min'] = $newSchemaInfo["min"] == null ? $frInput["min"] : $newSchemaInfo["min"];
        $schema['max'] = $newSchemaInfo["max"] == null ? $frInput["max"] : $newSchemaInfo["max"];
        $schema['unique'] = $schema['unique'] == null || $schema['unique'] == "N" ? false : true;
        $schema['nullable'] = $schema['nullable'] == null || $schema['nullable'] == "N" ? false : true;
        $schema['tableName'] = $newSchemaInfo['tableName'];
        $schema['columnName'] = $newSchemaInfo['columnName'];
        return $schema;
    }
    
    private function dropConstraints(string $constraintType, string $tableName, string $columnName ): void {
        $constraints = [];
        if($constraintType == "CHECK"){
            $constraints = $this->findCheckConstraintRelated($tableName, $columnName);
        } elseif ($constraintType == "UNIQUE"){
            $constraints = $this->findUniqueConstraintRelated($tableName, $columnName);
        } elseif ($constraintType == "PRIMARY KEY"){
            $constraints[] = $this->dbTarget->getTableByName($tableName)->getPK()->getName();
        } elseif ($constraintType == "FOREIGN KEY"){
            $constraints = $this->dbTarget->findForeignKeyRelated($tableName,$columnName);
        }

        foreach($constraint as $constraintName) {
            $this->dbTargetConnection->dropConstraint($tableName,$constraintName);
        }

    }

    private function findForeignKeyRelated(string $tableName, string $columnName): bool {
        $fks = $this->dbTarget->getTableByName($tableName)->getAllFK();
        $relatedFks = [];
        foreach ($fks as $fk) {
            $columns = $fk->getColumns();
            foreach($columns as $column){
                if($column['primary']['columnName'] == $columnName) {
                    $relatedFks[] = $fk;
                }
            }
        }
        return $relatedFks;
    }

    private function updateDbTargetEdit(array $newSchemaInfo, array $impactResult, array $frInput): void
    {
        
        $mainDbSchemaImpact = $impactResult['dbSchemaImpact'];
        $tableName = $mainDbSchemaImpact['tableName'];
        $columnName = $mainDbSchemaImpact['columnName'];

        if ($impactResult['instanceImpact'] == true) {
            $schemaInfo = $this->prepareBefore($newSchemaInfo,$frInput);
            $distinctValues = $this->dbTargetConnection->getDistinctValues($tableName, $columnName);
            $this->dbTargetConnection->addColumn($tableName, $columnName . "_temp", $schemaInfo);
            $randomDetail = $this->prepareBefore($newSchemaInfo,$frInput);

            $randomContext = new RandomContext(\strtolower($randomDetail['dataType']));
            $randomContext->random(\count($distinctValues)+1, $randomDetail, $randomDetail['unique']);
            $randomData = $randomContext->getRandomData();
            //dd($randomData);
            $this->dbTargetConnection->updateInstance($mainDbSchemaImpact['tableName'], $mainDbSchemaImpact['columnName'], $distinctValues, $mainDbSchemaImpact['columnName'] . "_temp", $randomData);
            $this->dbTargetConnection->dropColumn($mainDbSchemaImpact['tableName'], $mainDbSchemaImpact['columnName']);
            $this->dbTargetConnection->updateColumnName($mainDbSchemaImpact['tableName'], $mainDbSchemaImpact['columnName'] . "_temp", $mainDbSchemaImpact['columnName']);
            $this->dbTargetConnection->setNullable($mainDbSchemaImpact['tableName'], $mainDbSchemaImpact['columnName'],$schemaInfo);
        } else {
            $detail = $this->prepareBefore($newSchemaInfo,$frInput);
            $this->dbTargetConnection->updateColumn($mainDbSchemaImpact['tableName'], $mainDbSchemaImpact['columnName'], $detail);
            $this->dbTargetConnection->setNullable($mainDbSchemaImpact['tableName'], $mainDbSchemaImpact['columnName'],$detail);
        }
        if ( ($newSchemaInfo['unique'] == "Y" || $frInput['unique'] == "Y") && $newSchemaInfo['unique'] != "N" ) {
            //$this->dbTargetConnection->addUniqueConstraint($mainDbSchemaImpact['tableName'],$mainDbSchemaImpact['columnName']);
            foreach ($uniqueDrops as $unique) {
                $columns = $unique->getAllColumns();
                $this->dbTargetConnection->addUniqueConstraint($mainDbSchemaImpact['tableName'], implode(",", $columns));
            }
        }
        if ($newSchemaInfo['min'] != null || $newSchemaInfo['max'] != null || $frInput['min'] != null || $frInput['max'] != null) {
            $schemaInfo = $this->prepareBefore($newSchemaInfo,$frInput);
            $min = $schemaInfo['min'];
            $max = $schemaInfo['max'];
            $this->dbTargetConnection->addCheckConstraint($mainDbSchemaImpact['tableName'], $mainDbSchemaImpact['columnName'], $min, $max);
        }

    }

    public function

    private function updateDbTargetAdding(array $newSchemaInfo): void
    {
        if ($newSchemaInfo['tableName'] != null && $newSchemaInfo['columnName'] != null) {

            $this->dbTargetConnection->addColumn($newSchemaInfo['tableName'], $newSchemaInfo['columnName'], $newSchemaInfo);
            $pkColumn = $this->dbTarget->getTableByName($newSchemaInfo['tableName'])->getPK()->getColumns();
            //$pkColumn = $pkColumn[0];
            $pkColumn = $pkColumn[0];
            $distinctValues = $this->dbTargetConnection->getDistinctValues($newSchemaInfo['tableName'], $pkColumn);
            $randomContext = new RandomContext(\strtolower($newSchemaInfo['dataType']));
            $randomContext->random(\count($distinctValues)+1, $newSchemaInfo, $newSchemaInfo['unique'] == "Y" ? true : false);
            $randomData = $randomContext->getRandomData();
            $this->dbTargetConnection->updateInstance($newSchemaInfo['tableName'], $pkColumn, $distinctValues, $newSchemaInfo['columnName'], $randomData);
            if ($newSchemaInfo['min'] != null || $newSchemaInfo['max'] != null) {
                $this->dbTargetConnection->addCheckConstraint($newSchemaInfo['tableName'], $newSchemaInfo['columnName'], $newSchemaInfo['min'], $newSchemaInfo['max']);
            }
            
            if($newSchemaInfo['nullable'] == "N") {
                $newSchemaInfo['nullable'] = false;
                $this->dbTargetConnection->setNullable($newSchemaInfo['tableName'], $newSchemaInfo['columnName'],$newSchemaInfo);
            }
            if ($newSchemaInfo['unique'] == "Y") {
                $this->dbTargetConnection->addUniqueConstraint($newSchemaInfo['tableName'], $newSchemaInfo['columnName']);
            }

        }
    }

    private function updateDbTargetDeleting(array $newSchemaInfo, array $frinput): void
    {
        $ckDrops = $this->findCheckConstraintRelated($frinput['tableName'], $frinput['columnName']);

        foreach ($ckDrops as $check) {
            $this->dbTargetConnection->dropConstraint($frinput['tableName'], $check->getName());
        }

        $uniqueDrops = $this->findUniqueConstraintRelated($frinput['tableName'], $frinput['columnName']);

        foreach ($uniqueDrops as $unique) {
            $this->dbTargetConnection->dropConstraint($frinput['tableName'], $unique->getName());
        }

        $this->dbTargetConnection->dropColumn($mainDbSchemaImpact['tableName'], $mainDbSchemaImpact['columnName']);

    }

    private function findUniqueConstraintRelated(string $tableName, string $columnName): array
    {
        $uniqueConstraints = $this->dbTarget->getTableByName($tableName)->getAllUniqueConstraint();
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
        $checkConstraints = $this->dbTarget->getTableByName($tableName)->getAllCheckConstraint();
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

    private function findFrByNo(string $no, array $functionalRequirements): array
    {
        foreach ($functionalRequirements as $functionalRequirement) {
            if ($functionalRequirement['no'] == $no) {
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
            if ($this->findInstanceImpactByDataType($changeInput['dataType'], $dbColumn->getDataType()->getType())) {
                return true;
            }
        }
        if ($changeInput['length'] !== null) {
            $dbColumnLength = $dbColumn->getDataType()->getLength();
            if ($dbColumnLength != null) {
                if ($this->findInstanceImpactByLength($changeInput['length'], $dbColumnLength)) {
                    return true;
                }
            }
        }
        if ($changeInput['scale'] !== null) {
            $dbColumnScale = $dbColumn->getDataType()->getScale();
            if ($dbColumnScale != null) {
                if ($this->findInstanceImpactByScale($changeInput['scale'], $dbColumnScale)) {
                    return true;
                }
            }
        }
        if ($changeInput['unique'] !== null) {
            $changeUnique = $changeInput['unique'] == "N" ? false : true;
            if ($this->findInstanceImpactByUnique($changeUnique, $this->isColumnUnique($frInput['tableName'], $frInput['columnName']))) {
                return true;
            }
        }
        if ($changeInput['nullable'] !== null) {
            $dbColumnNullable = $dbColumn->isNullable();
            $changeNullable = $changeInput['nullable'] == "N" ? false : true;
            if ($this->findInstanceImpactByNullable($changeNullable, $dbColumnNullable)) {
                return true;
            }
        }
        if ($changeInput['min'] !== null) {
            $dbColumnMin = $this->getMin($frInput['tableName'], $frInput['columnName']);
            if ($this->findInstanceImpactByMin($changeInput['min'], $dbColumnMin)) {
                return true;
            }
        }
        if ($changeInput['max'] !== null) {
            $dbColumnMax = $this->getMax($frInput['tableName'], $frInput['columnName']);
            if ($this->findInstanceImpactByMax($changeInput['max'], $dbColumnMax)) {
                return true;
            }
        }
        return false;
    }

    private function analysisAdding(array $changeInput, array $frInput): bool
    {
        if (\is_null($changeInput['tableName']) || \is_null($changeInput['columnName'])) {
            return false;
        }
        $tables = $this->dbTarget->getAllTables();
        if (\array_key_exists($changeInput['tableName'], $tables)) {
            $columns = $tables[$changeInput['tableName']]->getAllColumns();
            if (\array_key_exists($changeInput['columnName'], $columns)) {
                
                return false;
            }
        }
        return true;
        
    }

    private function analysisDeleting(array $changeInput, array $frInput, array $functionalRequirements, string $frChangeNo): bool
    {
        if ($this->isPK($frInput['tableName'], $frInput['columnName'])) {
            return false;
        }
        foreach ($functionalRequirements as $fr) {
            foreach ($fr['inputs'] as $input) {
                if (($input['name'] == $frInput['name']) && ($fr['no'] != $frChangeNo)) {
                    return false;
                }
            }
        }
        return true;
    }

    private function isPK(string $tableName, string $columnName): bool
    {
        $table = $this->dbTarget->getTableByName($tableName);
        $pk = $table->getPK();
        return \in_array($columnName, $pk->getColumns());
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

    private function isColumnUnique(string $tableName, string $columnName): bool
    {
        $uniqueConstraints = $this->dbTarget->getTableByName($tableName)->getAllUniqueConstraint();
        foreach ($uniqueConstraints as $uniqueConstraint) {
            foreach ($uniqueConstraint->getColumns() as $column) {
                if ($column == $columnName) {
                    return true;
                }
            }
        }
        return false;
    }

    private function getMin(string $tableName, string $columnName)
    {
        $checkConstraints = $this->dbTarget->getTableByName($tableName)->getAllCheckConstraint();
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

    private function findInstanceImpactByMin($changeMin, $columnMin): bool
    {
        if ($columnMin == null) {
            return true;
        }

        return ($changeMin > $columnMin) ? true : false;
    }

    private function getMax(string $tableName, string $columnName)
    {
        $checkConstraints = $this->dbTarget->getTableByName($tableName)->getAllCheckConstraint();
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

}
