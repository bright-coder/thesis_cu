<?php

namespace App\Library\Datatype;

use App\Library\Datatype\DataType;
use App\Library\Datatype\DataTypeInterface;

class _Float implements DataTypeInterface {
    private $n;
    private $precision;

    public function __construct(int $n = 53){
        $this->n = $n;
        $this->precision = $this->n > 24 ? 7 : 15;
    }

    public function getType(): string{
        return DataType::FLOAT ;
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