<?php

namespace App\Library\Constraint;

use App\Library\Constraint\Constraint;
use App\Library\Constraint\ConstraintType;

class Check implements Constraint{
    
    private $name;
    private $checkColumns;
    private $definition;
    private $max = [];
    private $min = [];

    public function __construct(string $name = "",array $checkColumns = [],string $definition = ""){
        $this->name = $name;
        $this->checkColumns = $checkColumns;
        $this->definition = $definition;

        $this->extractMinMax();
    }

    public function getName(): string{
        return $this->name;
    }

    public function getType(): string{
        return ConstraintType::CHECK;
    }

    public function getColumns(): array{
        return $this->checkColumns;
    }

    public function getDetail(): array{
        return ['definition' => $definition,'min' => $min, '$max' => $max];
    }

    private function extractMinMax(): void{
       foreach ($this->checkColumns as $key => $value) {
           # code...
       }
    }

    private function extractFromOperator(string $column): string{ 
        return \strpos($this->definition,'['.$column.']');
    }

    private function checkPositionQuote():void {
        
    }

}