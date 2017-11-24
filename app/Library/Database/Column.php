<?php

namespace App\Library\Column;

use App\Library\DataType\DataType;

class Column
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var DataType
     */
    private $dataType;
    /**
     * @var bool
     */
    private $isNullable;
    /**
     * @var string
     */
    private $default;
    /**
     * @var int
     */
    private $numDistinctValues;

    public function __construct(array $columnInfo)
    {
        $this->name = $columnInfo['name'];

        $this->dataType =
        new DataType(
            $columnInfo['dataType'],
            [
                'length' => $columnInfo['length'],
                'precision' => $columnInfo['precision'],
                'scale' => $columnInfo['scale'],
            ]
        );

        $this->$default = $columnInfo['_default'];

        $this->$isNullable = $columnInfo['isNullable'] === "NO" ? false : true;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param DataType $dataType
     */
    public function setDataType(DataType $dataType): void
    {
        $this->dataType = $dataType;
    }

    public function getDataType(): Datatype
    {
        return $this->dataType;
    }

    /**
     * @param bool $isNullable
     */
    public function setNullable(bool $isNullable): void
    {
        $this->isNullable = $isNullable;
    }

    public function isNullable(): bool
    {
        return $this->isNullable;
    }

    public function setDefault(string $dafault): void
    {
        $this->default = $dafault;
    }

    public function getDefault(): string
    {
        return $this->default;
    }

    public function getNumDistinctValues(): int
    {
        return $this->numDistinctValues;
    }

}
