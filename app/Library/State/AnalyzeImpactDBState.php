<?php

namespace App\Library\State;

use App\Library\Builder\DatabaseBuilder;
use App\Library\CustomModel\DBTargetConnection;
use App\Library\CustomModel\DBTargetInterface;
use App\Library\State\StateInterface;
use App\Library\State\AnalyzeImpactFRState;
use App\Library\State\AnalyzeDBMethod\AnalyzeDBAdd;
use App\Library\State\AnalyzeDBMethod\AnalyzeDBEdit;
use App\Library\State\AnalyzeDBMethod\AnalyzeDBDel;

use App\Model\Project;
use App\Model\FunctionalRequirement;
use App\Model\FunctionalRequirementInput;
use App\Model\ChangeRequest;
use App\Model\ChangeRequestInput;
use App\Library\ChangeAnalysis;
use DB;
use App\Library\Database\Database;

class AnalyzeImpactDBState implements StateInterface
{
    /**
     * Undocumented variable
     *
     * @var DBTargetInterface
     */
    private $dbTargetConnection = null;
    /**
     * Undocumented variable
     *
     * @var Database
     */
    private $dbTarget = null;
    private $errorMessage = '';

    public function __construct()
    {
    }

    public function getStateName(): String
    {
        return 'AnalyzeImpactDBState';
    }

    public function analyze(ChangeAnalysis $changeAnalysis) : void
    {
        $projectId = $changeAnalysis->getProjectId();
        if ($this->connectTargetDB($projectId)) {
            $this->getDbSchema();
            if (!$this->validateChangeRequestInput($changeAnalysis->getAllChangeRequestInput())) {
                return;
            }
            
            foreach ($changeAnalysis->getAllChangeRequestInput() as $changeRequestInput) {
                $this->getDbSchema();
                switch ($changeRequestInput->changeType) {
                    case 'add':
                        $analyzer = new AnalyzeDBAdd($this->dbTarget, $changeRequestInput, $this->dbTargetConnection);
                        break;
                    case 'edit':
                        $analyzer = new AnalyzeDBEdit($this->dbTarget, $changeRequestInput, $this->dbTargetConnection);
                        break;
                    case 'delete':
                        $analyzer = new AnalyzeDBDel($this->dbTarget, $changeRequestInput, $this->dbTargetConnection);
                        break;
                    default:
                        # code...
                        break;

                }

            
                $analyzer->analyze();
                $analyzer->modify();
                
                $changeAnalysis->addDBImpactResult(
                    $changeRequestInput->id,
                    $analyzer->getSchemaImpactResult(),
                    $analyzer->getInstanceImpactResult()
                );
            }
            //dd($changeAnalysis->getDBImpactResult());
            $changeAnalysis->setState(new AnalyzeImpactFRState);
            $changeAnalysis->analyze();
        } else {
        }
    }

    private function validateChangeRequestInput(array $changeRequestInputList) : bool
    {
        $result = true;
        foreach ($changeRequestInputList as $changeRequestInput) {
            $changeRequestInput->status = 1;
            if ($changeRequestInput->changeType == 'edit') {
                $frInput = FunctionalRequirementInput::where('id', $changeRequestInput->functionalRequirementInputId)->first();
                $table = $this->dbTarget->getTableByName($frInput->tableName);
                
                if ($table->isPK($frInput->columnName)) {
                    if ($changeRequestInput->unique != null) {
                        $changeRequestInput->status = 0;
                        $changeRequestInput->errorMessage = 'Cannot change Unique at Primary key column.';
                    }
                    else if ($changeRequestInput->nullable != null) {
                        $changeRequestInput->status = 0;
                        $changeRequestInput->errorMessage = 'Cannot change Nullable at Primary key column.';
                    }
                }
                else if ($table->isFK($frInput->columnName)) {
                    if ($changeRequestInput->default != null) {
                        $changeRequestInput->status = 0;
                        $changeRequestInput->errorMessage = 'Cannot change Default at Foreign Key column.';
                    }
                    else if ($changeRequestInput->min != null) {
                        $changeRequestInput->status = 0;
                        $changeRequestInput->errorMessage = 'Cannot change Min at Foreign Key column.';
                    }
                    else if ($changeRequestInput->max != null) {
                        $changeRequestInput->status = 0;
                        $changeRequestInput->errorMessage = 'Cannot change Max at Foreign Key column.';
                    }
                }
            }
            else if($changeRequestInput->changeType == 'delete') {
                $frInput = FunctionalRequirementInput::where('id', $changeRequestInput->functionalRequirementInputId)->first();
                $table = $this->dbTarget->getTableByName($frInput->tableName);
            
                if($table->isPK($frInput->columnName)) {
                    $changeRequestInput->status = 0;
                    $changeRequestInput->errorMessage = 'Cannot delete Primary key column.';
                }
            }
            if($changeRequestInput->status == 0) {
                $result = false;
            }
            $changeRequestInput->save();
        }

        return $result;
    }

    private function connectTargetDB(string $projectId): bool
    {
        $project = Project::where('id', $projectId)->first();
        $this->dbTargetConnection = DBTargetConnection::getInstance(
            $project->dbType,
            $project->dbServer,
            $project->dbPort,
            $project->dbName,
            $project->dbUsername,
            $project->dbPassword
        );

        if (!$this->dbTargetConnection->connect()) {
            return false;
        }

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
}
