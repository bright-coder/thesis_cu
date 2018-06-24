<?php

namespace App\Library\Random;

use App\Library\Datatype\DataType;

use App\Library\Datatype\RandomInterface;

class RandomContext {
    /**
     * randomType
     *
     * @var RandomInterface
     */

    public static function getRandomData(int $numRows = 0, string $dataType, array $info = ['precision' => 0, 'scale' => 0 , 'length' => 0, 'min' => 0, 'max' => 0], bool $isUnique = false): array {
        $randomType;
        switch ($dataType) {
            case DataType::CHAR :
            case DataType::VARCHAR :
                $randomType = new RandomString();
                break;
            case DataType::NCHAR :
            case DataType::NVARCHAR :
                $randomType = new RandomNString();
                break;
            case DataType::FLOAT :
            case DataType::REAL :
                $randomType = new RandomFloat();
                break;
            case DataType::DECIMAL :
                $randomType = new RandomDecimal();
                break;
            case DataType::DATE :
                $randomType = new RandomDate();
                break;
            case DataType::DATETIME :
                $randomType = new RandomDateTime();
                break;
            default:
                $randomType = new RandomInteger();
                break;
        }
        
        $randomType->random($numRows,$info,$isUnique);
        return $randomType->getRandomData();
    }

}