<?php

namespace App\Library\Datatype;

use App\Library\Datatype\DataTypeInterface;

class Char implements DataTypeInterface {
    private $type;
    private $length;

    public function __construct(string $type,int $length){
        $this->type = $type;
        $this->length = ($length > 8000 || $length < 1) ? 8000 : $length;
    }

    public function getType(): string{
        return $this->type;
    }

    public function getLength()
    {
        return $this->length;
    }

    public function getPrecision()
    {
        return null;
    }

    public function getScale()
    {
        return null;
    }
}