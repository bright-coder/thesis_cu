<?php

namespace App\Library\Datatype;

class Decimal{
    private $precision;
    private $scale;

    public function __construct(int $precision = 18, int $scale = 0){
        $this->precision = $precision;
        $this->scale = $scale;
    }

    public function getType(): string{
        return "decimal";
    }

    public function getPrecision(): int{
        return $this->precision;
    }

    public function getScale(): int{
        return $this->scale;
    }
}