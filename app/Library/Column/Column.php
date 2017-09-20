<?php

namespace App\Library\Column;

class Column{
    
    private $name,$datatype,$isNullable = FALSE,$default = NULL,$numRow;
    private $constraint;

    public function construct(string $name,Dataype $datatype){
        $this->name = $name;
        $this->datatype = $datatype;
        $this->constraint = ['Unique' => new Unique(), 'Check' => new Check() ];
    }

    public function getName(): string{
        return $this->name;
    }

    public function getDataype(): Datatype{
        return $this->datatype;
    }

    public function setNumRow(int $numRow): void{
        $this->numRow =$numRow;
    }

    public function getNumRow(): int{
        return $this->numRow;
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

    public function setNullable(boolean $isNullable): void{
        $this->isNullable = $isNullable;        
    }

    public function isNullable(): boolean{
        return $this->isNullable;
    }

    public function setDefault(string $dafault): void{
        $this->default = $dafault;
    }

    public function getDefault(): string{
        return $this->default;
    }

}