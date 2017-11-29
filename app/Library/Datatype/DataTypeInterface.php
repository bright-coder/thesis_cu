<?php

namespace App\Library\Datatype;

interface DataTypeInterface {
    public function getType(): string;
    public function getDetails(): array;
}
    