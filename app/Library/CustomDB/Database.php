<?php

namespace App\Library\CustomDB;

class Database
{
    private $obj;
    private $dbname;
    private $tables = [];

    public function __construct(string $server,string $dbname,string $user,string $pass,string $type = 'sqlsrv'){
        if($type == 'sqlsrv'){
            $this->obj = new SqlServer($server,$dbname,$user,$pass);
        }
        elseif($type == 'mysql'){
            $this->obj = new Mysql($server,$dbname,$user,$pass);
        }

        $this->dbname = $database;
    }

    public function getName(): string{
        return $this->dbname;
    }

    public function getType(): string{
        return $this->obj->getType();
    }

    public function getTables(): array{
        return $this->tables;
    }

}
