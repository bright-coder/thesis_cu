<?php
 
 namespace App\Library\Random;

 use App\Library\Random\RandomInterface;

 class RandomNString implements RandomInterface {
    private $randomData;

    public function __construct() {
        $this->randomData = [];
    }

    public function random(int $numRows, array $info, bool $isUnique): void {
        $maxLength = $info['length'];
        $characters ='กขฃคฅฆงจฉชซฌญฎฏฐฑฒณดตถทธนบปผฝพฟภมยรลวศษสหฬอฮ0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $arrayOfChars = preg_split('//u', $characters, null, PREG_SPLIT_NO_EMPTY);
        if (!$isUnique) {
            while(true){
                $length = \rand(1,$maxLength);
                shuffle($arrayOfChars);
                $randomChars = implode("",$arrayOfChars);
                $r = mb_substr($randomChars,0,$length,"UTF-8");
                    while(mb_strlen($r) < $length) {
                        shuffle($arrayOfChars);
                        $randomChars = implode("",$arrayOfChars);
                        $r .= mb_substr($randomChars,0, $length - mb_strlen($r),"UTF-8");
                    }
                    $this->randomData[] = $r;
                if (sizeof($this->randomData) == $numRows) { break; }
            }
        }
        else {
            while(true){
                $length = \rand(1,$maxLength);
                shuffle($arrayOfChars);
                $randomChars = implode("",$arrayOfChars);
                $r = mb_substr($randomChars,0,$length,"UTF-8");
                    while(mb_strlen($r) < $length) {
                        shuffle($arrayOfChars);
                        $randomChars = implode("",$arrayOfChars);
                        $r .= mb_substr($randomChars,0, $length - mb_strlen($r),"UTF-8");
                    }
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