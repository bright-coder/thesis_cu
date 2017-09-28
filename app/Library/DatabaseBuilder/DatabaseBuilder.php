<?php

namespace App\Library\DatabaseBuilder;

use App\Library\Database\Database;
use App\Library\Table\Table;
use App\Library\Column\Column;

use App\Library\CustomModel\DBConnector;

class DatabaseBuilder{
    
    /**
    * @var Database
    */
    private $database;

    /**
    * @var DBConnector
    */
    private $DBConnector;

    public function __construct(DBConnnector $DBConnector){
        $this->DBConnector = $DBConnector;
        $this->database = new Database($this->DBConnector->getDBServer(),$this->DBConnector->getDBName());
    }

    public function getDatabase(): Database{
        return $this->database;
    }

    public function setTable(): void{
        $tables = \array_flip($this->DBConnector->getAllTables());
        foreach ($tablesName as $name) {
            $column = new Column();
        }
        $this->database->setTables();
    }

}
