<?php

namespace App\Library\Datatype;

class _Float{
    private $n;

    public function __construct(int $n = 53){
        $this->n = $n;
    }

    public function getType(): string{
        return "float";
    }

    public function getPrecision(): int{
        return $this->n > 24 ? 7 : 15;
    }

    public function getN(): int{
        return $this->n;
    }
}