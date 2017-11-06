<?php

namespace App\Library\DataTypeInterface;

interface DataTypeInterface {
    public function getType(): string;
    public function getDetails(): array;
}
    