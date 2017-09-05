<?php

namespace App\Library\Table;

class Table{
    private $name;
    private $columns = NULL;
    private $pkColumns = NULL;
    private $fkColumns = NULL;

    public function __construct(string $name){
        $this->name = $name;
    }

    public function addColumn(Column $col): void{
        if($this->columns === NULL) $columns = [];
        $this->columns[$col->getName()] = $col;
    }

    public function addColumns(array $cols = []): void{
        if($this->columns === NULL) $columns = [];
        $this->columns = array_merge($this->columns,$cols);
    }

    public function getColumns(): array{
        return $this->columns;
    }

    private function addPkcolumn(array $pkCols){
    }


}