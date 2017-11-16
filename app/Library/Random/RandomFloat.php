<?php

namespace App\Library\Random;

use App\Library\Random\RandomInterface;

class RandomFloat implements RandomInterface {
    private $randomData;

    public function __construct() {
        $this->randomData = [];
    }

    public function random(int $numRows, array $info, bool $isUnique): void {
        
        $this->checMinMaxPrecision(floatval($info['min']),floatval($info['max']),$info['precision']);
        $min = $info['min'];
        $max = $info['max'];
        $precision = $info['precision'];
        
        $range = $max - $min;
        $rangeAvg = $range/5;
        $scale = 0;
        //if( ) {
            $scale = $precision - strlen(explode(".",strval($precision))[0]);
        //}
        $step = pow(10, $scale);

        if(!$isUnique) {
            $max = $min + $rangeAvg;
            for($i = 0; $i < 5; ++$i){
                while(sizeof($this->randomData) < $numRows * (0.2 * ($i+1)) ){
                    $r = mt_rand($min * $step, $max * $step) / $step;
                        $this->randomData[] = $r.'';
                }
                $min = $max ;
                $max = $min + $rangeAvg;
            }
        }
        else {
            if ($numRows > intval($range)+1 && $numRows > $range * $step +1 ) {
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
                        $r = round($r, $decimals - strlen(strval(intval($max))) );
                        if(!isset($this->randomData[$r.""])){
                            $this->randomData[$r.""] = false;
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

    private function checMinMaxPrecision(float $min, float $max, int $precision): void {
        $precisionMin = strlen(str_replace(".", "", strval($min)));
        $precisionMax = strlen(str_replace(".", "", strval($max)));
        $realPrecision = $precision;
        if($precisionMin > $realPrecision) {
            throw new \Exception("Error : min precision is more than precision", 1);
            
        }
        if($precisionMax > $realPrecision) {
            throw new \Exception("Error : max precision is more than precision", 1);
        }

    }

}