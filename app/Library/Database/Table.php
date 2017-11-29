<?php

namespace App\Library\Database;

use App\Library\Column\Column;
use App\Library\Constraint\Constraint;
use App\Library\Constraint\ForeignKey;
use App\Library\Constraint\PrimaryKey;

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
     * @var ForeignKey
     */
    private $fks;
    /**
     * @var array
     */
    private $constraints;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->columns = [];
        $this->pk = new PrimaryKey();
        $this->fks = []; // array of foreign key
        $this->constraints = [];
    }

    public function addColumn(Column $col): void
    {
        $this->columns[$col->getName()] = $col;
    }

    public function addColumns(array $cols = []): void
    {
        $this->columns = array_merge($this->columns, $cols);
    }

    public function getAllColumns(): array
    {
        return $this->columns;
    }

    public function getColumnByName(string $colName)
    {
        return $this->columns[$colName];
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

    public function getFKbyName(string $name): ForeignKey
    {
        return $this->fks[$name];
    }

    public function getAllFK(): array
    {
        return $this->fks;
    }

    public function addConstraint(Constraint $constraint): void
    {
        $this->constraints[$constraint->getName()] = $constraint;
    }

    public function getConstraintByName(string $name): Constraint
    {
        return $this->constraints[$name];
    }

    public function getAllConstraints(): array
    {
        return $this->constraints;
    }

}
