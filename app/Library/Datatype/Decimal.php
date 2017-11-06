<?php

namespace App\Library\Datatype;

use App\Library\Datatype\DataType;
use App\Library\Datatype\DataTypeInterface;

class Decimal implements DataTypeInterface{
    private $precision;
    private $scale;

    public function __construct(int $precision = 18, int $scale = 0){
        $this->precision = $precision;
        $this->scale = $scale;
    }

    public function getType(): string{
        return DataType::DECIMAL;
    }

    public function getDetails(): array{
        return ['precision' => $this->precision, 'scale' => $this->scale];
    }

}