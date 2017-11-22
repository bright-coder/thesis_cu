<?php

namespace App\Library\Random;

use App\Library\Random\RandomInterface;

class RandomDate implements RandomInterface
{
    private $randomData;

    public function __construct()
    {
        $this->randomData = [];
    }

    public function random(int $numRows, array $info, bool $isUnique): void
    {
        $min = strtotime('1970-01-01');
        $max = strtotime('now');

        if (!$isUnique) {

            while (sizeof($this->randomData) < $numRows) {
                $val = rand($min, $max);
                $this->randomData[] = date('Y-m-d', $val);
            }

        } else {

            if (intval(date_diff(date_create('1970-01-01'), date_create('now'))->format("%a")) >= $numRows) {
                $min = \DateTime::createFromFormat('Y-m-d', '1970-01-01');
                while (sizeof($this->randomData) < $numRows) {
                    $this->randomData[] = $min->format('Y-m-d');
                    $min->modify('+1 day');
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
