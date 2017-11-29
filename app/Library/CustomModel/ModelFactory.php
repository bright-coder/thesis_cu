<?php

namespace App\Library\CustomModel;

use App\Library\CustomModel\DBConnector;
use App\Library\CustomModel\SqlServer;
use App\Library\CustomModel\Mysql;

final class ModelFactory{

    /**
    * @return DBConnector
    */
    public static function getInstance(string $dbType,string $server, string $database, string $user, string $pass): DBConnector{
        if($dbType == "sqlsrv") {
            SqlServer::setConnectionInfo($server,$database,$user,$pass);
            return $sq
        }
        return new SqlServer($server,$database,$user,$pass);

        else return new Mysql($server,$database,$user,$pass);
    }

}