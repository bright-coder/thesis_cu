<?php

namespace App\Library\FunctionalRequirement;

use App\Library\DataType\Datatype;

class FRInput
{

    private $id;
    private $name;
    private $dataType;
    private $default;
    private $isNullable;
    private $isUnique;
    private $min = null;
    private $max = null;

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

    public function getDataType(): DataType
    {
        return $this->dataType;
    }

    public function setDataType(DataType $dataType): void
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

    public function setNullable(\boolean $isNullable): void
    {
        $this->isNullable = $isNullable;
    }

    public function isUnique(): bool
    {
        return $this->isUnique;
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

}
