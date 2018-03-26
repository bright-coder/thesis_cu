<?php

namespace App\Library\Builder;

use App\Library\CustomModel\DBTargetInterface;
use App\Library\Database\Database;

class DatabaseBuilder
{

    /**
     * @var Database
     */
    private $database;

    /**
     * @var DBTargetInterface
     */
    private $DBConnector;

    /**
     * @param DBConnnector $DBConnector
     */
    public function __construct(DBTargetInterface $DBConnector)
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
        foreach ($tables as $table) {
            $columns = $this->DBConnector->getAllColumnsByTableName($table->getName());
            $tables[$table->getName()]->setColumns($columns);
            $tables[$table->getName()]->setInstance($this->DBConnector->getInstanceByTableName($table->getName()));
            $constraints = $this->DBConnector->getAllConstraintsByTableName($table->getName());
            $tables[$table->getName()]->setPK($constraints['primaryKey']);
            $tables[$table->getName()]->setFK($constraints['foreignKeys']);
            $tables[$table->getName()]->setUniqueConstraints($constraints['uniqueConstraints']);
            $tables[$table->getName()]->setCheckConstraints($constraints['checkConstraints']);
        }
        $this->database->setTables($tables);
    }

}
