<?php

namespace App\Library\RandomContext;

use App\Library\Datatype\DataType;

class RandomContext {

    private $randomType = NULL;

    public function __construct(string $dataType) {
        
        switch ($dataType) {
            case DataType::CHAR :
            case DataType::VARCHAR :
                $this->randomType = new RandomString();
                break;
            case DataType::NCHAR :
            case DataType::NVARCHAR :
                $this->randomType = new RandomNString();
            case DataType::FLOAT :
                $this->randomType = new Random
            default:
                # code...
                break;
        }
    }

}