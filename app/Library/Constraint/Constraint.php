<?php

namespace App\Library\Constraint;

interface Constraint{

    public function getName(): string;
    public function getType(): string;
    public function getColumns(): array;
    public function getDetail(): array;


}