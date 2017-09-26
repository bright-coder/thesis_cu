<?php

namespace App\Library\CustomModel;

use App\Library\CustomModel\DBConnector;

class Mysql implements DBConnector {

    private $conObj;
    private $server;
    private $database;
    
    public function __construct($server,$database,$user,$pass){
        $this->conObj = new \PDO("mysql:host={$server};dbname={$database}",$user,$pass);

        $this->server = $server;
        $this->database = $database;
    }

    public function getDBType(): string{
        return "mysql";
    }

}