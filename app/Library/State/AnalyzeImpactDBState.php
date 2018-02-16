<?php

namespace App\Library\State;

use App\Library\Builder\DatabaseBuilder;
use App\Library\CustomModel\DBTargetConnection;
use App\Library\State\ChangeAnalysis;
use App\Library\State\StateInterface;
use DatabaseSchemaColumn;
use DatabaseSchemaTable;
use DB;

class AnalyzeImpactDBState implements StateInterface
{
    private $dbTargetConnection = null;
    private $dbTarget;

    public function getStateNo() : int {
        return 2;
    }

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
            //$this->getDbSchema();
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

        DB::beginTransaction();
        try {
            foreach ($this->dbTarget->getAllTables() as $table) {
                $dbSchemaTable = new DatabaseSchemaTable;
                $dbSchemaTable->projectId = AbstractState::$projectId;
                $dbSchemaTable->name = $table->getName();
                $dbSchemaTable->save();

                foreach ($table->getAllColumns() as $column) {
                    $dbSchemaColumn = new DatabaseSchemaColumn;

                }

            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
        }
    }

}
