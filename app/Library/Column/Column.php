<?php

namespace App\Library\Column;

class Column{
    
    private $name,$datatype,$isNullable = FALSE,$default = NULL,$numRow;
    private $constraint = ['Unique' => NULL,'Check' => NULL];

    public function construct(SchemaDB $info){

    }

    public function getName(): string{
        return $this->name;
    }

    public function isUnique(): boolean{
        return $this->constraint['Unique'] === NULL ? FALSE : TRUE;
    }

    public function getUnique(): Unique{
        return $this->constraint['Unique'];
    }

    public function setUnique(Unique $unique): void{
        $this->constraint['Unique'] = $unique;
    }

    public function isCheck(): boolean{
        return $this->constraint['Check'] === NULL ? FALSE : TRUE;
    }

    public function getCheck(): Check{
        return $this->constraint['Check']; 
    }

    public function setCheck(Check $check): void{
        $this->constraint['Check'] = $check;
    }

    public function isNullable(): boolean{
        return $this->isNullable;
    }

    public function getDefault(): string{
        return $this->default;
    }

}