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
    /**
     * @var array
     */
    private $instance;

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

    public function setInstance(array $instance): void
    {
        $this->instance = $instance;
    }

    public function getInstance(): array
    {
        return $this->instance;
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
        if(\array_key_exists($name,$this->columns)) {
            return $this->columns[$name];
        }
        return null;
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

    public function getFKByColumnName(string $columnName): ForeignKey
    {
        $fks = $this->getAllFK();
        foreach ($fks as $fk) {
            foreach ($fk->getColumns() as $link) {
                if ($link['from']['columnName'] == $columnName) {
                    return $fk;
                }
            }
        }
    }

    public function isPK(string $columnName): bool
    {
        return \in_array($columnName, $this->pk->getColumns());
    }

    public function isFK(string $columnName): bool
    {
        $fks = $this->getAllFK();
        foreach ($fks as $fk) {
            foreach ($fk->getColumns() as $link) {
                if ($link['from']['columnName'] == $columnName) {
                    return true;
                }
            }
        }
        return false;
    }

    public function isUnique(string $columnName): bool
    {
        $uniqueConstraints = $this->getAllUniqueConstraint();
        foreach ($uniqueConstraints as $uniqueConstraint) {
            foreach ($uniqueConstraint->getColumns() as $column) {
                if ($column == $columnName) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getMin(string $columnName)
    {
        $checkConstraints = $this->getAllCheckConstraint();
        foreach ($checkConstraints as $checkConstraint) {
            foreach ($checkConstraint->getColumns() as $column) {
                if ($column == $columnName) {
                    $minAllColumn = $checkConstraint->getDetail()['min'];
                    if (\array_key_exists($columnName, $minAllColumn)) {
                        return $minAllColumn[$columnName];
                    }
                }
            }
        }
        return null;
    }

    public function getMax(string $columnName)
    {
        $checkConstraints = $this->getAllCheckConstraint();
        foreach ($checkConstraints as $checkConstraint) {
            foreach ($checkConstraint->getColumns() as $column) {
                if ($column == $columnName) {
                    $maxAllColumn = $checkConstraint->getDetail()['max'];
                    if (\array_key_exists($columnName, $maxAllColumn)) {
                        return $maxAllColumn[$columnName];
                    }
                }
            }
        }
        return null;
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
