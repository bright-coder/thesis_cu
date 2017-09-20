<?php

namespace App\Library\Constraint;

class Unique{
    private $name;

    public function __construct(string $name = ""){
        $this->name = $name;
    }

    public function getName(): string{
        return $this->name;
    }
}