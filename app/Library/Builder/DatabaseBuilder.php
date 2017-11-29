<?php

namespace App\Library\DatabaseBuilder;

use App\Library\CustomModel\DBConnector;
use App\Library\Database\Database;

class DatabaseBuilder
{

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
    public function __construct(DBConnector $DBConnector)
    {
        $this->DBConnector = $DBConnector;
        $this->database = new Database($this->DBConnector->getDBServer(), $this->DBConnector->getDBName());
    }

    public function getDatabase(): Database
    {
        return $this->database;
    }

    public function setUpTablesAndColumns(): void
    {
            $tables = $this->DBConnector->getAllTables();
                foreach($tables as $table) {
                    $columns = $this->DBConnector->getAllColumnsByTableName($table->getName());
                    $tables[$table->getName()]->setColumns($columns);
                }
            $this->database->setTables($tables);
    }

}
