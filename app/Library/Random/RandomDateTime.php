<?php

namespace App\Library\Random;

use App\Library\Random\RandomInterface;

class RandomDateTime implements RandomInterface
{
    private $randomData;

    public function __construct()
    {
        $this->randomData = [];
    }

    public function random(int $numRows, array $info, bool $isUnique): void
    {
        $min = strtotime('1970-01-01 00:00:00');
        $max = strtotime('now');

        if (!$isUnique) {
            while (sizeof($this->randomData) < $numRows) {
                $val = rand($min, $max);
                $this->randomData[] = strval(date('Y-m-d H:i:s', $val)).".000";
            }
        } else {

            if ($max - $min >= $numRows) {
                $min = \DateTime::createFromFormat('Y-m-d H:i:s', '1970-01-01 00:00:00');
                while (sizeof($this->randomData) < $numRows) {
                    $this->randomData[] = strval($min->format('Y-m-d H:i:s'))."000";
                    $min->modify('+1 second');
                }

            } else {
                throw new Exception("Error : Cannot generate {$numRows} unique values.", 1);
            }

        }

    }

    public function getRandomData(): array
    {
        return $this->randomData;
    }

}
