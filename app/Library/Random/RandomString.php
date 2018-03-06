<?php
 
 namespace App\Library\Random;

 use App\Library\Random\RandomInterface;

 class RandomString implements RandomInterface {
    private $randomData;

    public function __construct() {
        $this->randomData = [];
    }

    public function random(int $numRows, array $info, bool $isUnique): void {
        $maxLength = $info['length'];
        $characters ='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        if (!$isUnique) {
            while(true){
                $length = \rand(1,$maxLength);
                $r = substr(str_shuffle(str_repeat($characters, ceil($length/strlen($characters)) )),1,$length);
                    $this->randomData[] = $r;
                if (sizeof($this->randomData) == $numRows) { break; }
            }
        }
        else {
            while(true){
                $length = \rand(1,$maxLength);
                $r = substr(str_shuffle(str_repeat($characters, ceil($length/strlen($characters)) )),1,$length);
                if(!isset($randomData[$r])){
                    $this->randomData[$r] = false;
                }
                if (sizeof($this->randomData) == $numRows) { break; }
            }
        }
    }

    public function getRandomData(): array{
        return $this->randomData;
    }
 }