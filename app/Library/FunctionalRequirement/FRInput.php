<?php

namespace App\Library\FunctionalRequirement;

use App\Library\DataType\DataTypeInterface;

class FRInput
{

    private $id;
    private $name;
    private $dataType;
    private $default = null;
    private $isNullable;
    private $isUnique;
    private $min = null;
    private $max = null;
    private $table;
    private $column;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDataType(): DataTypeInterface
    {
        return $this->dataType;
    }

    public function setDataType(DataTypeInterface $dataType): void
    {
        $this->dataTye = $dataType;
    }

    public function getDefault(): string
    {
        return $this->default;
    }

    public function setDefault(string $default): void
    {
        $this->default = $default;
    }

    public function isNullable(): bool
    {
        return $this->isNullable;
    }

    public function setNullable(bool $isNullable): void
    {
        $this->isNullable = $isNullable;
    }

    public function isUnique(): bool
    {
        return $this->isUnique;
    }

    public function setUnique(bool $isUnique): void
    {
        $this->isUnique = $isUnique;
    }

    public function getMin(): string
    {
        return $this->$min;
    }

    public function setMin(string $min): void
    {
        $this->min = $min;
    }

    public function getMax(): string
    {
        return $this->$max;
    }

    public function setMax(string $max): void
    {
        $this->max = $max;
    }

    public function setTable(string $table): void
    {
        $this->table = $table;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function setColumn(string $column): void
    {
        $this->column = $column;
    }

    public function getColumn(): string
    {
        return $this->column;
    }

}
