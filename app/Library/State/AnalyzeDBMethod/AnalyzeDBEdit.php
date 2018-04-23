<?php

namespace App\Library\State\AnalyzeDBMethod;
use App\Library\State\AnalyzeDBMethod\AbstractAnalyzeDBMethod;
use App\Model\ChangeRequestInput;
use App\Library\Database\Database;
use App\Library\CustomModel\DBTargetConnection;

class AnalyzeDBEdit extends AbstractAnalyzeDBMethod {
    
    public function construct(Database $database, ChangeRequestInput $changeRequestInput) {
        $this->database = $database;
        $this->$changeRequestInput = $changeRequestInput;
    }

    public function analyze(Database $database, ChangeRequestInput $changeRequestInput): bool {
        $this->changeRequestInput = $changeRequestInput;

        $table = $database->getTableByName($changeRequestInput->tableName);

        // if not have column in table
        if(!$table->getColumnbyName($changeRequestInput->columnName)) {
            return true;
        }
        return false;
    }

    private function modify(DBTargetConnection $dbTargetConnection): bool {
        $dbTargetConnection->addColumn($changeRequestInput);
    }

}