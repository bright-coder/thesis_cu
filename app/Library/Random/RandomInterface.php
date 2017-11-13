<?php

namespace App\Library\Random;

interface RandomInterface {
    public function random(int $numRows, array $info, bool $isUnique): void;
    public function getRandomData(): array;

}