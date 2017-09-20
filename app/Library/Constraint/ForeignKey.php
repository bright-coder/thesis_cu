<?php

namespace App\Library\Constraint;

class ForeignKey{
    private $name,$refFrom,$refTo;

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

    public function getRefFrom(): array{
        return $this->refFrom;
    }

    public function getReferTo(): array{
        return $this->refTo;
    }

    public function getType(): string{
        return "FK";
    }



}