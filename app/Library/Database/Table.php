<?php

namespace App\Library\Database;

use App\Library\Column\Column;
use App\Library\Constraint\ConstraintType;
use App\Library\Constraint\ForeignKey;
use App\Library\Constraint\PrimaryKey;
use App\Library\Constraint\Unique;
use App\Library\Constraint\Check;

class Table
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var array
     */
    private $columns;
    /**
     * @var PrimaryKey
     */
    private $pk;
    /**
     * @var array
     */
    private $fks;
    /**
     * @var array
     */
    private $uniqueConstraints;
    /**
     * @var array
     */
    private $checkConstraints;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->columns = [];
        $this->pk = new PrimaryKey();
        $this->fks = []; // array of foreign key
        $this->uniqueConstraints = []; // array of uniqueConstraints;
        $this->checkConstraints = []; // array of checkConstraints;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function addColumn(Column $column): void
    {
        $this->columns[$column->getName()] = $column;
    }

    public function setColumns(array $columns): void
    {
        $this->columns = $columns;
    }

    public function getAllColumns(): array
    {
        return $this->columns;
    }

    public function getColumnByName(string $name)
    {
        return $this->columns[$name];
    }

    public function setPK(PrimaryKey $pk): void
    {
        $this->pk = $pk;
    }

    public function getPK(): PrimaryKey
    {
        return $this->pk;
    }

    public function addFK(ForeignKey $fk): void
    {
        $this->fks[$fk->getName()] = $fk;
    }

    public function setFK(array $fks): void
    {
        $this->fks = $fks;
    }

    public function getFKbyName(string $name): ForeignKey
    {
        return $this->fks[$name];
    }

    public function getAllFK(): array
    {
        return $this->fks;
    }

    public function setUniqueConstraints(array $uniqueConstraints): void
    {
        $this->uniqueConstraints = $uniqueConstraints;
    }

    public function addUniqueConstraint(Unique $uniqueConstraint): void
    {
        $this->uniqueConstraints[$uniqueConstraint->getName()] = $uniqueConstraint;
    }

    public function getUniqueConstraintByName(string $name): Unique
    {
        return $this->uniqueConstraints[$name];
    }

    public function getAllUniqueConstraint(): array {
        return $this->uniqueConstraints;
    }

    public function setCheckConstraints(array $checkConstraints): void
    {
        $this->checkConstraints = $checkConstraints;
    }

    public function addCheckConstraint(array $checkConstraint): void
    {
        $this->checkConstraints[$checkConstraint->getName()] = $checkConstraint;
    }

    public function getCheckConstraintByName(string $name): Unique
    {
        return $this->checkConstraints[$name];
    }

    public function getAllCheckConstraint(): array {
        return $this->checkConstraints;
    }


}
