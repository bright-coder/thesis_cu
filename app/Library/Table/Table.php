<?php

namespace App\Library\Table;

class Table{
    private $name;
    private $columns;
    private $pk = NULL;
    private $fk = NULL;

    public function __construct(string $name){
        $this->name = $name;
        $this->columns = [];
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

    public function setFK(ForeignKey $fk): void{
        $this->fk = $fk;
    }

    public function getFK(): ForeignKey{
        return $this->fk;
    }

}