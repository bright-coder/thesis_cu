<?php

namespace App\Library\State;

use App\Library\Builder\DatabaseBuilder;
use App\Library\CustomModel\DBTargetConnection;
use App\Library\State\ChangeAnalysis;
use App\Library\State\StateInterface;
use App\DatabaseSchemaColumn;
use App\DatabaseSchemaTable;
use DB;

class AnalyzeImpactDBState implements StateInterface
{
    private $dbTargetConnection = null;
    private $dbTarget = null;
    private $message = null;

    public function process(ChangeAnalysis $changeAnalysis): bool
    {
        $connectDbInfo = $changeAnalysis->getRequest()['connectDbInfo'];
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
            if( $this->saveDbSchema( $changeAnalysis->getProjectId() )) {
                    //analysis();
            }else {
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

    private function saveDbSchema(int $projectId): bool {
        DB::beginTransaction();
        try {
            foreach ($this->dbTarget->getAllTables() as $table) {
                $dbSchemaTable = new DatabaseSchemaTable;
                $dbSchemaTable->projectId = $projectId;
                $dbSchemaTable->name = $table->getName();
                $dbSchemaTable->save();

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
