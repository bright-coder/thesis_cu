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

class AnalyzeImpactDBState implements StateInterface
{
    private $dbTargetConnection = null;
    private $dbTarget = null;
    private $message = null;

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

        $changeFunctionRequirement = $this->findFrByNo($changeRequest['functionalRequirementNo'], $functionalRequirements);

        foreach ($changeRequest['inputs'] as $changeInput) {

            if ($changeInput['changeType'] == "edit") {
                $impact = $this->analysisEditing($changeInput, $this->findInputByName($changeInput['name'], $changeFunctionRequirement));

            } elseif ($changeInput['changeType'] == "add") {
                $impact = $this->analysisAdding($changeInput, $this->findInputByName($changeInput['name'], $changeFunctionRequirement));
            } else {
                $impact = $this->analysisDeleting($changeInput, $this->findInputByName($changeInput['name'], $changeFunctionRequirement));
            }
        }

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
            $dbColumnMin = $this->
        }
        if ($changeInput['max'] !== null) {

        }
    }

    private function analysisAdding(array $changeInput, array $frInput): array
    {

    }

    private function analysisDeleting(array $changeInput, array $frInput): array
    {

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
        
    }

}
