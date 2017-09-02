<?php

namespace App\Library\CustomDB;

class SqlServer {
        
    private $conObj;
    private $tables = [];
    private $dbname;

    public function __construct($server,$database,$user,$pass){
        $this->conObj = new \PDO("sqlsrv:server={$server} ; Database = {$database}",$user,$pass);
        $this->dbname = $database;
    }

    public function getName(){
        return $this->dbname;
    }

    public function getType(): string{
        return "sql server";
    }

    public function getTables(): array{
        return $this->tables;
    }

}