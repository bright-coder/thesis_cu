<?php

namespace App\Library\Datatype;

interface DataTypeInterface {
    public function getType(): string;
    public function getlength();
    public function getPrecision();
    public function getScale();
}
    