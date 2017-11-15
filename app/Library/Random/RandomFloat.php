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
        $decimals = $info['precision'];
        
        $range = $max - $min;
        $rangeAvg = $range/5;
        $scale = pow(10, $decimals);

        if(!$isUnique) {
            $max = $min + $rangeAvg;
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
            if ($numRows > intval($range)+1 && $numRows > $range * $scale +1 ) {
                throw new \Exception("Invalid range", 1);
            }
            else if($numRows == intval($range)+1 ) {
                for($i = $min; $i <= $max ; ++$i) {
                    $this->randomData[$i] = false;
                }
            }
            else if($numRows == $range * $scale +1 ) {
                for($i = 0; $i < $numRows ; ++$i) {
                    $this->randomData[$min+(1/$scale)*$i.""] = false;
                }
            }
            else {
                $max = $min + $rangeAvg;
                for($i = 0; $i < 5; ++$i){
                    while(sizeof($this->randomData) < $numRows * (0.2 * ($i+1)) ){
                        $r = mt_rand($min * $scale, $max * $scale) / $scale;
                        if(!isset($this->randomData[$r.""])){
                            $this->randomData[$r.""] = 0;
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