<?php

namespace App\Library\Constraint;

class Check{
    
    private $name;
    private $rawDetail;
    private $detail = ['min' => null, 'max' => null];

    public function __construct($name,$rawDetail){
        $this->name = $name;
        $this->rawDetail = $rawDetail;

        $this->extractDetail();
    }

    public function getName(){
        return $this->name;
    }

    public function getMin(){
        return $this->detail['min'];
    }

    public function getMax(){
        return $this->detail['max'];
    }

    private function extractDetail(): void{
        // do something
    }

}