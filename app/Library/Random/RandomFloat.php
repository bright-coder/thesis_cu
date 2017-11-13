<?php

namespace App\Library\Random;

use App\Library\Random\RandomInterface;

class RandomFloat implements RandomInterface {
    private $randomData;

    public function __construct() {
        $this->randomData = [];
    }

    public function random(int $numRows, array $info, bool $isUnique): void {
        $min = $info['min'];
        $max = $info['max'];
        $decimals = $info['decimals'];
        
        $range = $max - $min;
        $rangeAvg = $range/5;
        $scale = pow(10, $decimals);
        $max = $min + $rangeAvg;

        if(!$isUnique) {
            for($i = 0; $i < 5; ++$i){
                while(sizeof($this->randomData) < $numRows * (0.2 * ($i+1)) ){
                    $r = mt_rand($min * $scale, $max * $scale) / $scale;
                        $this->randomData[] = $r.'';
                }
                $min = $max ;
                $max = $min + $rangeAvg;
            }
        }
        else {
            for($i = 0; $i < 5; ++$i){
                while(sizeof($this->randomData) < $numRows * (0.2 * ($i+1)) ){
                    $r = rand($min,$max);
                    if(!isset($this->randomData[$r])){
                        $this->randomData[$r] = $r.'';
                    }
                }
                $min = $max ;
                $max = $min + $rangeAvg;
            }
        }
    }

    public function getRandomData(): array {
        return $this->randomData;
    }

}