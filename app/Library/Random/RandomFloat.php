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
            $num = 1.5;
            $count = 1;
            $numAr = ["1.5"];
            while ($num < 10.9) {
                if($num < 10)
                $num = round($num+0.01,2);
                else
                $num = round($num+0.1,1);
                ++$count;
                $numAr[] = $num."";
            }
            $this->randomData[] = $count;
            $this->randomData[] = $numAr;


            // $min = 0.5;
            // $max = 0.9;
            // $precision = 2;

            // $scaleMin = $precision - strlen(explode(".",strval($min))[0]);
            // $scaleMax = $precision - strlen(explode(".",strval($max))[0]);
            
            // // case 0.50 => "5" that must be "50"
            // $strMaxDecimal = "0";
            // if(strpos(".",$strMaxDecimal)) {
            //     $strMaxDecimal = explode(".",strval($max))[1];
            //     $diffScale = $scaleMax - strlen($strMaxDecimal);
            //     if($diffScale > 0) {
            //         for($i=0; $i < $diffScale ; ++$i){
            //             $strMaxDecimal .= "0";
            //         }
            //     }
            // }
            
            // $possibleValues = (intval($max) - intval($min)) * pow(10,$scaleMin) + intval(substr($strMaxDecimal,0,$scaleMax)) + 1;
            // $this->randomData[] = $possibleValues;


        //     if ($numRows > intval($range)+1 && $numRows > $range * $step +1 ) {
        //         throw new \Exception("Invalid range", 1);
        //     }
        //     // else if($numRows == intval($range)+1 ) {
        //     //     for($i = $min; $i <= $max ; ++$i) {
        //     //         $this->randomData[$i] = false;
        //     //     }
        //     // }
        //     else if($numRows == $range * $step +1 ) {
        //         for($i = 0; $i < $numRows ; ++$i) {
        //             $this->randomData[$min+(1/$step)*$i.""] = false;
        //         }
        //     }
        //     else {
        //         $max = $min + $rangeAvg;
        //         $step = pow(10,$precision);
        //         for($i = 0; $i < 5; ++$i){
                    
        //             while(sizeof($this->randomData) < $numRows * (0.2 * ($i+1)) ){
        //                 $r = mt_rand($min * $step, $max * $step) / $step;
        //                 $r = round($r, $precision - strlen(explode(".",strval($r))[0])  );
        //                 if(!isset($this->randomData[$r.""])){
        //                     $this->randomData[$r.""] = strlen(strval($r));
        //                 }
        //             }
        //             $min = $max ;
        //             $max = $min + $rangeAvg;
        //         }
        //     }
            
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