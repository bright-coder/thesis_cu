<?php

namespace App\Library\Constraint;

class Unique{
    private $name;

    public function __construct($name = "null"){
        $this->name = $name;
    }

    public function getName(){
        return $this->name;
    }
}