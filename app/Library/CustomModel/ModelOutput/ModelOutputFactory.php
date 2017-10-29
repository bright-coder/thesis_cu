<?php

namespace App\Library\CustomModel\ModelOutput;

use App\Library\CustomModel\ModelOutput\ModelOutputType;

use App\Library\Database\Column;
use App\Library\Constraint\ConstraintFactory;


final class ModelOutputFactory{

    public static function createOutput(int $OutputType, array $queryResult): array{

        if (ModelOutputType::CONSTRAINT === $OutputType) {
            $constraints = [];
            foreach ($queryResult as $row) {
                if (!array_key_exists($row['name'],$constraints)) {

                    $constraints[$row['name']] = $row;

                    $tempColumnName = $constraints[$row['name']]['columnName'];
                    
                    $constraints[$row['name']]['columnName'] = [$tempColumnName];
                }
                else {
                    array_push($constraints[$row['name']]['columnName'], $row['columnName']);
                }
                 
            }

            foreach ($constraints as $constraint) {
                $constraints[$constraint['name']] = ConstraintFactory::create($constraint);
            }

            return $constraints;
            
        }
        elseif (ModelOutputType::COLUMN === $OutputType) {
            
        }
    
    }

}