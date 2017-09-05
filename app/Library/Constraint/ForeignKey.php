<?php

namespace App\Library\Constraint;

class ForeignKey{
    private $name,$refFrom,$refTo;

    public function __construct($name,
        $refFrom = ['table' => null, 'column' => null],
        $refTo = ['table' => null, 'column' => null]){
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

    public function getRefTo(): array{
        return $this->refTo;
    }

    public function getType(): string{
        return "FK";
    }



}