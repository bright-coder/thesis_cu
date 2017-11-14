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
                break;
            case DataType::FLOAT :
                $this->randomType = new RandomFloat();
                break;
            case DataType::DECIMAL :
                $this->randomType = new RandomDecimal();
                break;
            case DataType::DATE :
                $this->randomType = new RandomDate();
                break;
            case DataType::DATETIME :
                $this->randomType = new RandomDateTime();
                break;
            default:
                $this->randomType = new RandomInteger();
                break;
        }
    }

    public function random(int $numRows = 0, array $info = ['precision' => 0, 'scale' => 0 , 'length' => 0, 'min' => 0, 'max' => 0], bool $isUnique = false): void {
        $this->randomType->random($numRows,$info,$isUnique);
    }

    public function getRandomData(): array {
        return $this->randomType->getRandomData();
    }

}