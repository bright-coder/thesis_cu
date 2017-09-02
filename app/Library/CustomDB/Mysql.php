<?php

namespace App\Library\CustomDB;

class Mysql {

    private $conObj;
    
    public function __construct($server,$database,$user,$pass){
        $this->conObj = new \PDO("mysql:host={$server};dbname={$database}",$user,$pass);
    }

    public function getDbType(): string{
        return "mysql";
    }

}