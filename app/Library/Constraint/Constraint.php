<?php

namespace App\Library\Constraint;

interface Constraint{
    const PRIMARY_KEY = "PRIMARY KEY";
    const FOREIGN_KEY = "FOREIGN KEY";
    const UNIQUE = "UNIQUE";
    const CHECK = "CHECK";

    public function getName(): string;
    public function getType(): string;
    public function getColumns(): array;
    public function getDetail(): array;


}