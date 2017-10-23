<?php

namespace App\Library\Constraint;

use App\Library\Constraint\Constraint;

class PrimaryKey implements Constraint {
    private $name;
    /**
     * @var array
     */
    private $pkColumns;

    public function __construct(string $name = "",array $pkColumns = []){
        $this->name = $name;
        $this->pkColumns = $pkColumns;
    }

    public function getName(): string{
        return $this->name;
    }

    public function getType(): string{
        return Constraint::PRIMARY_KEY;
    }

    public function getColumns(): array{
        return $this->pkColumns();
    }

    public function getDetail(): array{
        return [];
    }

}