<?php

namespace App\Library\Column;

use App\Library\DataType\DataType;
use App\Library\Constraint\Unique;
use App\Library\Constraint\Check;

class Column{
    /**
     * @var string
     */
    private $name;
    /**
     * @var DataType
     */
    private $dataType;
    /**
     * @var bool
     */
    private $isNullable;
    /**
     * @var string
     */
    private $default;
    /**
     * @var array
     */
    private $constraint;

    public function __construct(){
        $this->constraint = ['Unique' => NULL,'Check' => NULL];
    }

    public function setName(string $name): void{
        $this->name = $name;
    }

    public function getName(): string{
        return $this->name;
    }

    /**
     * @param DataType $dataType
     */
    public function setDataType(DataType $dataType): void{
        $this->dataType = $dataType;
    }

    public function getDataType(): Datatype{
        return $this->dataType;
    }

    /**
    * @param bool $isNullable
    */
    public function setNullable(bool $isNullable): void{
        $this->isNullable = $isNullable;        
    }

    public function isNullable(): bool{
        return $this->isNullable;
    }

    /**
     * @param string $dafault
     */
    public function setDefault(string $dafault): void{
        $this->default = $dafault;
    }

    public function getDefault(): string{
        return $this->default;
    }

    public function isUnique(): bool{
        return $this->constraint['Unique'] === NULL ? FALSE : TRUE;
    }

    /**
     * @param Unique $unique
     */
    public function setUnique(Unique $unique): void{
        $this->constraint['Unique'] = $unique;
    }

    public function getUnique(): Unique{
        return $this->constraint['Unique'];
    }

    public function isCheck(): bool{
        return $this->constraint['Check'] === NULL ? FALSE : TRUE;
    }
    
    public function getCheck(): Check{
        return $this->constraint['Check']; 
    }
    
    /**
     * @param Check $check
     */
    public function setCheck(Check $check): void{
        $this->constraint['Check'] = $check;
    }

}