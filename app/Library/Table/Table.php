<?php

namespace App\Library\Table;

use App\Library\Column\Column;
use App\Library\Constraint\ForeignKey;
use App\Library\Constraint\PrimaryKey;

class Table{
    private $name;
    private $columns;
    private $pk;
    private $fk;

    public function __construct(string $name){
        $this->name = $name;
        $this->columns = [];
        $this->pk = new PrimaryKey();
        $this->fk = []; // array of foreign key
    }

    public function addColumn(Column $col): void{
        $this->columns[$col->getName()] = $col;
    }

    public function addColumns(array $cols = []): void{
        $this->columns = array_merge($this->columns,$cols);
    }

    public function getAllColumns(): array{
        return $this->columns;
    }    
    
    public function getColumnByName(string $colName){
        return $this->columns[$colName];
    }

    public function setPK(PrimaryKey $pk): void{
        $this->pk = $pk;
    }

    public function getPK(): PrimaryKey{
        return $this->pk;
    }

    public function addFK(ForeignKey $fk): void{
        $this->fk[$fk->getName()] = $fk;
    }

    public function getFKbyName(string $name): ForeignKey{
        return $this->fk[$name];
    }

    public function getAllFK(): array{
        return $this->fk;
    }

}