<?php

namespace App\Library\Datatype;

class Nchar{
    private $type;
    private $length;

    public function __construct(string $type,int $length){
        $this->type = $type;
        $this->length = $length > 4000 ? 4000 : $length;
    }

    public function getType(): string{
        return $this->type;
    }

    public function getLength(): int{
        return $this->length;
    }
}