<?php

namespace App\Library\Constraint;

class Check{
    
    private $name;
    private $rawDetail;
    private $detail = ['min' => null, 'max' => null];

    public function __construct($name = "null", $rawDetail = ""){
        $this->name = $name;
        $this->rawDetail = $rawDetail;

        $this->extractMaxMin();
    }

    public function getName(): string{
        return $this->name;
    }

    public function getMin(): int{
        return $this->detail['min'];
    }

    public function getMax(): int{
        return $this->detail['max'];
    }

    private function extractMaxMin(): void{
        // do something
    }

}