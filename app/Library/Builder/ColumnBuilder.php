<?php

namespace App\Library\Builder;

use App\Library\Column\Column;
use App\Library\DataType\DataType;

class ColumnBuilder{

    /**
    * @var Column
    */
    private $column;

    public function createColumn(): void{
        $this->column = new Column();
    }

    public function setBasicInfo(array $basicInfo): void{
        $this->column->setName($basicInfo['name']);
        $this->column->setDataType(new DataType($basicInfo['']));
        $this->column->setDefault();
        $this->column->setNullable();
    }

    public function setConstraint(): void{
        $this->column->setUnique();
        $this->column->setCheck();
    }

    public function getColumn(): Column{
        return $this->column;
    }

}