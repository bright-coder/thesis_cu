<?php

namespace App\Library\State;

use App\Library\State\AbstractState;

use App\Library\CustomModel\DBTargetConnection;
use App\Library\Builder\DatabaseBuilder;
use DB;

class AnalyzeImpactDBState extends AbstractState
{

    public function analysis(array $connectDbInfo,array $changeRequest) : bool {
        
        $dbTargetConnection = DBTargetConnection::getInstance(
            $connectDbInfo["type"],
            $connectDbInfo["hostName"],
            $connectDbInfo["dbName"],
            $connectDbInfo["username"],
            $connectDbInfo["password"]
        );
        if( !$dbTargetConnection->connect() ) {
            $this->message = "Cannot Connect to Target Database";
            $this->statusCode = 303;
            return false;
        }
        else {
            $databaseBuilder = new DatabaseBuilder($dbTargetConnection);
            $databaseBuilder->setUpTablesAndColumns();
        
        }
        $dbTarget = $databaseBuilder->getDatabase();
        foreach ($dbTarget->getAllTables() as $table) {
            $this->message .= $table->getName();
        }
        //$this->message = implode(" ",$dbTarget->getAllTables());
        $this->statusCode = 303;
        return true;
    }

}
