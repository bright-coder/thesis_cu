<?php

namespace App\Library\CustomModel;

use App\Library\CustomModel\DBTargetInterface;
use App\Library\CustomModel\Mysql;
use App\Library\CustomModel\SqlServer;

class DBTargetConnection
{

    /**
     * @var DBTargetInterface
     */
    private static $connector = null;

    public static function getInstance(string $dbType, string $server, string $database, string $user, string $pass): DBTargetInterface
    {

        if (DBTargetConnection::$connector === null) {

            if ($dbType == "sqlsrv") {
                DBTargetConnection::$connector = new SqlServer($server, $database, $user, $pass);
            } else {
                DBTargetConnection::$connector = new Mysql($server, $database, $user, $pass);
            }

        }

        return DBTargetConnection::$connector;

    }

}
