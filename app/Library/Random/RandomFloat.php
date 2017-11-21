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
        ///$scale = 0;
        
            $scale = $precision - strlen(explode(".",strval($max))[0]);
        
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
            
            $num = $min;
            $this->randomData[] = $min.""; 
            while (sizeof($this->randomData) < $numRows && $num < $max) {
                $num = round($num+(1/pow(10, $precision - strlen( explode(".",strval($num))[0] ))), $precision - strlen( explode(".",strval($num))[0] ));
                $this->randomData[] = $num.""; 
            }
            if(sizeof($this->randomData) < $numRows ) {
                throw new \Exception("Error : Cannot generate {$numRows} unique values.", 1);
                
            }
  
         }
    }

    public function getRandomData(): array {
        return $this->randomData;
    }

    private function checMinMaxPrecision(float $min, float $max, int $precision): void {
        $precisionMin = strlen(str_replace(".", "", strval($min)));
        $precisionMax = strlen(str_replace(".", "", strval($max)));

        if($precisionMin > $precision) {
            throw new \Exception("Error : min precision is more than precision", 1);
            
        }
        if($precisionMax > $precision) {
            throw new \Exception("Error : max precision is more than precision", 1);
        }

    }

}