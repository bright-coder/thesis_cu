<?php

namespace App\Library\Datatype;

use App\Library\Datatype\DataTypeInterface;

class _Default implements DataTypeInterface
{
    private $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }
    
    public function getType(): string
    {
        return $this->type;
    }

    public function getDetails(): array
    {
        return [];
    }

}
