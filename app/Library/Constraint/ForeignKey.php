<?php

namespace App\Library\Constraint;

use App\Library\Constraint\Constraint;
use App\Library\Constraint\ConstraintType;

class ForeignKey implements Constraint{
    private $name;
    private $refFrom;
    private $refTo;

    public function __construct(string $name = "", 
    array $refFrom = ['table' => NULL, 'column' => NULL ], 
    array $refTo = ['table' => NULL, 'column' => NULL]){
            $this->name = $name;
            $this->refFrom = $refFrom;
            $this->refTo = $refTo;
    }
    
    public function getName(): string{
        return $this->name;
    }

    public function getType(): string{
        return ConstraintType::FOREIGN_KEY;
    }

    public function getColumns(): array{
        return [];
    }

    public function getDetail(): array{
        return ['refFrom' => $refFrom, 'refTo' => $refTo];
    }



}