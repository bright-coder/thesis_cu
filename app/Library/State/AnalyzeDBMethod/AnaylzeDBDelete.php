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
        $this->functionalRequirementInput = $this->findFunctionalRequirementInputById($changeRequestInput->functionRequirementInputId);
    }

    public function analyze(): bool {

        $table = $this->database->getTableByName($this->functionalRequirementInput->tableName);
        $column = $table->getColumnByName($this->functionalRequirementInput->columnName);

        if($this->database->isLinked($table->getName(),$column->getName()) ) {

        }
        else {
            $this->schemaImpactResult[0] = 
            [
                'tableName' => $table->getName(),
                'columnName' => $column->getName(),
                'oldSchema' => null,
                'newSchema' => null
            ];
            $this->instanceImpactResult[0] = [
                'oldInstance' => $this->dbTargetConnection->getInstanceByTableName($table->getName()),
                'newInstance' => null
            ];
        }
        


        return false;
    }

    private function modify(DBTargetInterface $dbTargetConnection): bool {
        //$dbTargetConnection->addColumn($changeRequestInput);
    }

}