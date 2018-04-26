<?php 

namespace App\Library\State\AnalyzeDBMethod;
use App\Library\State\AnalyzeDBMethod\AbstractAnalyzeDBMethod;
use App\Model\ChangeRequestInput;
use App\Library\Database\Database;
use App\Library\CustomModel\DBTargetInterface;

class AnalyzeDBDelete extends AbstractAnalyzeDBMethod {
    
    public function __construct(Database $database, ChangeRequestInput $changeRequestInput, DBTargetInterface $dbTargetConnection)
    {
        $this->database = $database;
        $this->changeRequestInput = $changeRequestInput;
        $this->dbTargetConnection = $dbTargetConnection;
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

    private function modify(DBTargetInterface $dbTargetConnection): bool {
        $dbTargetConnection->addColumn($changeRequestInput);
    }

}