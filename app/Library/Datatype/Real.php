<?php

namespace App\Library\Datatype;

use App\Library\Datatype\DataType;
use App\Library\Datatype\DataTypeInterface;

class Real implements DataTypeInterface {
    private $n;
    private $precision;

    public function __construct(int $n = 24){
        $this->n = $n;
        $this->precision = 7;
    }

    public function getType(): string{
        return DataType::REAL ;
    }

    public function getLength()
    {
        return null;
    }

    public function getPrecision()
    {
        return $this->precision;
    }

    public function getScale()
    {
        return null;
    }
}