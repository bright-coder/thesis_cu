<?php

namespace App\Library\CustomDB;

class Database
{
    private $obj;

    public function __construct($server,$database,$user,$pass,$type = 'sqlsrv'){
        if($type == 'sqlsrv'){
            $this->obj = new SqlServer($server,$database,$user,$pass);
        }
        elseif($type == 'mysql'){
            $this->obj = new Mysql($server,$database,$user,$pass);
        }
    }

    public function getName(){
        return $this->obj->getName();
    }

    public function getType(){
        return $this->obj->getType();
    }

    public function getTables(){
        return $this->obj->getTables();
    }

}
