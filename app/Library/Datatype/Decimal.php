<?php

namespace App\Library\Datatype;

use App\Library\Datatype\DataType;
use App\Library\Datatype\DataTypeInterface;

class Decimal implements DataTypeInterface{
    private $precision;
    private $scale;

    public function __construct(int $precision = 18, int $scale = 0){
        $this->precision = ($precision > 38 || $precision < 1) ? 38 : $precision;
        $this->scale = ($scale > $this->precision || $scale < 1 ) ? 0 : $scale ;
    }

    public function getType(): string{
        return DataType::DECIMAL;
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
        return $this->scale;
    }

}