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
        $this->column->setDataType(new DataType($basicInfo['dataType'],
            ['length' => $basicInfo['length'], 'precision' => $basicInfo['precision'], 'scale' => $basicInfo['scale'] ]));
        $this->column->setDefault($basicInfo['_default']);
        $this->column->setNullable($basicInfo['isNullable'] === "NO" ? FALSE : TRUE);
    }

    public function setConstraint(array $constraint): void{
        $this->column->setUnique();
        $this->column->setCheck();
    }

    public function getColumn(): Column{
        return $this->column;
    }

}