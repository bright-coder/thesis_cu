<?php

namespace App\Library\Constraint;

class PrimaryKey{
    private $name,$pkColumns;

    public function __construct($name,$pkColumns = []){
        $this->name = $name;
        $this->pkColumns = $pkColumns;
    }

    public function getName(): string{
        return $this->name;
    }

    public function getType(): string{
        return "PK";
    }

    public function getPkColumns(): array{
        return $this->pkColumns;
    }
}