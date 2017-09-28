<?php

namespace App\Library\Database;

use App\Library\Table\Table;

class Database{
    /**
    * @var string
    */
    private $server;
    /**
    * @var string
    */
    private $name;
    /**
    * @var array
    */
    private $tables;
    
    /**
    * @param string $server
    * @param string $name
    */
    public function __construct(string $server, string $name){
        $this->server = $server;
        $this->name = $name;

        $tables = [];
    }

    public function setTables(array $tables): void{
        $this->tables = $tables;
    }

    public function getAllTables(): array{
        return $this->tables;
    }

    public function getTableByName(string $name): Table{
        return $this->tables[$name];
    }

}