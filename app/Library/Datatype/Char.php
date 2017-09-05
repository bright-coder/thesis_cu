<?php

namespace App\Library\Datatype;

class Char{
    private $type;
    private $length;

    public function __construct(string $type,int $length){
        $this->type = $type;
        $this->length = $length > 8000 ? 8000 : $length;
    }

    public function getType(): string{
        return $this->type;
    }

    public function getLength(): int{
        return $this->length;
    }
}