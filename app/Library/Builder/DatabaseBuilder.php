<?php

namespace App\Library\DatabaseBuilder;

use App\Library\Database\Database;
use App\Library\Table\Table;
use App\Library\Column\Column;
use App\Library\Datatype\DataType;

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

    /**
    * @param DBConnnector $DBConnector
    */
    public function __construct(DBConnector $DBConnector){
        $this->DBConnector = $DBConnector;
        $this->database = new Database($this->DBConnector->getDBServer(),$this->DBConnector->getDBName());
    }

    public function getDatabase(): Database{
        return $this->database;
    }

    public function setTable(): void{
        $tables = \array_flip($this->DBConnector->getAllTables());
        foreach($tables as $name => $value){
            $tables[$name] = new Table($name);
            foreach($this->DBConnector->getAllColumns($name) as $column){
                $col = new Column($column->COLUMN_NAME,new DataType($column->DATA_TYPE,
                ['length' => $column->CHARACTER_MAXIMUM_LENGTH,
                'precision' => $column->NUMERIC_PRECISION,
                'scale' => $column->NUMERIC_SCALE]));
                $col->setNullable($column->IS_NULLABLE == "N0" ? FALSE : TRUE);
                $tables[$name]->addColumn($col);
            }
        }
        $this->database->setTables($tables);
    }


}
