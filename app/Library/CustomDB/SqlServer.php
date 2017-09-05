<?php

namespace App\Library\CustomDB;

class SqlServer {
    private $conObj;

    public function __construct($server,$database,$user,$pass){
        $this->conObj = new \PDO("sqlsrv:server={$server} ; Database = {$database}",$user,$pass);
    }

    public function getType(): string{
        return "sqlsrv";
    }

}