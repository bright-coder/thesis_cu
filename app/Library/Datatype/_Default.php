<?php

namespace App\Library\Datatype;

class _Default{
    private $type;

    public function __construct(string $type){
        $this->type = $type;
    }

    public function getType(): string{
        return $this->type;
    }

}