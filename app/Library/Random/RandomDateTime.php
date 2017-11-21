<?php

namespace App\Library\Random;

use App\Library\Random\RandomInterface;

class RandomDateTime implements RandomInterface {
    private $randomData;

    public function __construct() {
        $this->randomData = [];
    }

    public function random(int $numRows, array $info, bool $isUnique): void {
        $min = strtotime('1 January 1970');
        $max = strtotime('now');


        if(!$isUnique) {
            while(sizeof($this->randomData) < $numRows){
                $val = rand($min,$max);
                $this->randomData[] = date('Y-m-d H:i:s',$val);
            }
        }
        else {
            while(sizeof($this->randomData) < $numRows){
                $val = rand($min,$max);
                if(!isset($this->randomData[date('Y-m-d H:i:s',$val)])){
                        $this->randomData[date('Y-m-d H:i:s',$val)] = false;
                }
            }
        }
    }

    public function getRandomData(): array {
        return $this->randomData;
    }

}