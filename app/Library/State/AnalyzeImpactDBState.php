<?php

namespace App\Library\State;

use App\Library\State\AbstractState;

use DatabaseSchemaTable;
use DatabaseSchemaColumn;
use App\Library\CustomModel\DBTargetConnection;
use App\Library\Builder\DatabaseBuilder;
use DB;

class AnalyzeImpactDBState extends AbstractState
{
    private $dbTargetConnection = null;
    private $dbTarget;

    public function __construct(){
        $this->dbTargetConnection = DBTargetConnection::getInstance(
            $connectDbInfo["type"],
            $connectDbInfo["hostName"],
            $connectDbInfo["dbName"],
            $connectDbInfo["username"],
            $connectDbInfo["password"]
        );
    }

    public function analysis(array $connectDbInfo,array $changeRequest) : bool {
    
        if( !$this->dbTargetConnection->connect() ) {
            $this->message = "Cannot Connect to Target Database";
            $this->statusCode = 303;
            return false;
        }
        else {
            $this->getDbSchema();
        
        }
        foreach ($dbTarget->getAllTables() as $table) {
            $this->message .= $table->getName();
        }

        $this->statusCode = 303;
        return true;
    }

    private function getDbSchema() : void {
        $databaseBuilder = new DatabaseBuilder($this->dbTargetConnection);
        $databaseBuilder->setUpTablesAndColumns();
        $this->dbTarget = $databaseBuilder->getDatabase();

        DB::beginTransaction();
        try {
            foreach ($this->dbTarget->getAllTables() as $table) {
                
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
        }
    }

}
