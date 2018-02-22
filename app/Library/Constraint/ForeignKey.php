<?php

namespace App\Library\Constraint;

use App\Library\Constraint\Constraint;
use App\Library\Constraint\ConstraintType;

class ForeignKey implements Constraint
{
    private $name;
    private $links;

    public function __construct(string $name = "", array $links)
    {
        $this->name = $name;
        $this->links = $links;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return ConstraintType::FOREIGN_KEY;
    }

    public function getColumns(): array
    {
        return $this->links;
    }

    public function getDetail(): array
    {
        return [];
    }

}
