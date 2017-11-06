<?php

namespace App\Library\Datatype;

use App\Library\Datatype\DataType;
use App\Library\Datatype\DataTypeInterface;

class _Float implements DataTypeInterface {
    private $n;

    public function __construct(int $n = 53){
        $this->n = $n;
    }

    public function getType(): string{
        return DataType::FLOAT ;
    }

    public function getDetails(): array{
        return ['n' => $this->n, 'precision' => $this->n > 24 ? 7 : 15 ];
    }

}