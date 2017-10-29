<?php

namespace App\Library\Constraint;

use App\Library\Constraint\Constraint;
use App\Library\Constraint\ConstraintType;


class Unique implements Constraint{
    private $name;
    private $uniqueColumns;

    public function __construct(string $name = "",array $uniqueColumns = []){
        $this->name = $name;
        $this->uniqueColumns = $uniqueColumns;
    }

    public function getName(): string{
        return $this->name;
    }

    public function getType(): string{
        return ConstraintType::UNIQUE;
    }

    public function getColumns(): array{
        return $this->uniqueColumns;
    }

    public function getDetail(): array{
        return [];
    }
}