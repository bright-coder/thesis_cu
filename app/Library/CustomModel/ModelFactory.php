<?php

namespace App\Library\CustomModel;

use App\Library\CustomModel\DBConnector;
use App\Library\CustomModel\SqlServer;
use App\Library\CustomModel\Mysql;

class ModelFactory{

    public static function create(string $dbType,string $server, string $database, string $user, string $pass): DBConnector{
        if($dbType == "sqlsrv")
        return new SqlServer($server,$database,$user,$pass);

        else return new Mysql($server,$database,$user,$pass);
    }
}