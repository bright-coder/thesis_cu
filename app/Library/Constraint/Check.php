<?php

namespace App\Library\Constraint;

use App\Library\Constraint\Constraint;
use App\Library\Constraint\ConstraintType;

class Check implements Constraint{
    
    private $name;
    private $checkColumns;
    private $definition;
    private $max = [];
    private $min = [];

    public function __construct(string $name = "",array $checkColumns = [],string $definition = ""){
       
        $this->name = $name;
        $this->checkColumns = $checkColumns;
        $this->definition = $definition;

        $this->extractMinMax();
    }

    public function getName(): string{
        return $this->name;
    }

    public function getType(): string{
        return ConstraintType::CHECK;
    }

    public function getColumns(): array{
        return $this->checkColumns;
    }

    public function getDetail(): array{
        return ['definition' => $definition,'min' => $min, '$max' => $max];
    }

    private function extractMinMax(): void{

       foreach ($this->checkColumns as $columnName) {
           $this->extractMin($columnName);
       }

    }

    private function extractMin($columnName): void{
       
        $pattern = "\[{$columnName}\]>=\(([\d.]+?)\)";
        preg_match_all("/$pattern/", $this->definition, $values , PREG_SET_ORDER);

        if ($values !== false && !empty($values)) {

            $this->min[$columnName] = $values[0][1];
            for ($i = 1 ; $i < count($values) ; ++$i) {
                if ($values[$i][1] < $this->min[$columnName] ) {
                    $this->min[$columnName] = $values[$i][1];
                }
            }

        }
        elseif (condition) {
            # code...
        }
    

    }

}