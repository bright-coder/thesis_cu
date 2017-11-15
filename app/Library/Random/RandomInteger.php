<?php

namespace App\Library\Random;

use App\Library\Random\RandomInterface;

class RandomInteger implements RandomInterface {
    private $randomData;

    public function __construct() {
        $this->randomData = [];
    }

    public function random(int $numRows, array $info, bool $isUnique): void {
        $min = $info['min'];
        $max = $info['max'];
        $range = $max-$min;
        $rangeAvg = $range/5;

        if(!$isUnique) {
            $max = $min + $rangeAvg;
            for($i = 0; $i < 5; ++$i){
                while(sizeof($this->randomData) < $numRows * (0.2 * ($i+1))){
                    $r = rand($min,$max);
                    $this->randomData[] = $r.'';
                }
                $min = $max;
                $max = $min + $rangeAvg;
            }
        }
        else {
            if ($range+1 < $numRows) {
                throw new \Exception("Invalid range", 1);
            }
            else if ($range+1 == $numRows) {
                for($i = $min; $i <= $max ; ++$i){
                    $this->randomData[$i] = false;
                }
            }
            else {
                $max = $min + $rangeAvg;
                for($i = 0; $i < 5; ++$i){
                    while(sizeof($this->randomData) < $numRows * (0.2 * ($i+1)) ){
                        $r = rand($min,$max);
                        if(!isset($this->randomData[$r])){
                            $this->randomData[$r] = false;
                        }
                    }
                    $min = $max ;
                    $max = $min + $rangeAvg;
                }
            }
        }
    }

    public function getRandomData(): array {
        return $this->randomData;
    }

}