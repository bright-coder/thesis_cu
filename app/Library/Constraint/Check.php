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

        $this->findMinMaxAllColumns();
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
        return ['definition' => $this->definition,'min' => $this->min, 'max' => $this->max];
    }

    private function findMinMaxAllColumns(): void{

       foreach ($this->checkColumns as $columnName) {
           $this->findMin($columnName);
           $this->findMax($columnName);
       }

    }

    private function findMin($columnName): void{
        
        $patterns = [];
        $patterns[0] = "\[{$columnName}\]>=\(([\d.]+?)\)";
        $patterns[1] = "\(([\d.]+?)\)<=\[{$columnName}\]";
        $patterns[2] = "\[{$columnName}\]>\(([\d.]+?)\)";
        $patterns[3] = "\(([\d.]+?)\)<\[{$columnName}\]";

        $values = $this->findValuesByPatterns($patterns);

        if ( !empty($values) ) {
            $tempMin = ['value' => $values[0][1], 'isNotBound' => $values['isNotBound']];
            unset($values['isNotBound']); //prevent error in for loop

            for ($i = 1 ; $i < count($values) ; ++$i) {

                if ($values[$i][1] < $tempMin['value'] ) {
                    $tempMin['value'] = $values[$i][1];
                }

            }
            $this->min[$columnName] = $tempMin;

        }
    
    }

    private function findMax($columnName): void{

        $patterns = [];
        $patterns[0] = "\[{$columnName}\]<=\(([\d.]+?)\)";
        $patterns[1] = "\(([\d.]+?)\)>=\[{$columnName}\]";
        $patterns[2] = "\(([\d.]+?)\)>\[{$columnName}\]";
        $patterns[3] = "\[{$columnName}\]<\(([\d.]+?)\)";

        $values = $this->findValuesByPatterns($patterns);

        if( !empty($values) ) {

            $tempMax = ['value' => $values[0][1], 'isNotBound' => $values['isNotBound']];
            unset($values['isNotBound']); //prevent error in for loop
            for ($i = 1 ; $i < count($values) ; ++$i) {

                if ($values[$i][1] > $tempMax['value'] ) {
                    $tempMax['value'] = $values[$i][1];
                }

            }
            $this->max[$columnName] = $tempMax;

        }
    }

    private function findValuesByPatterns(array $patterns): array{

        foreach ($patterns as $index => $pattern) {
            $values = $this->extractValuesFromDefinition($pattern);
    
            if (!empty($values) && $values !== false) { 
                
                $values['isNotBound'] = false;
                
                if ($index > 1) {
                    $values['isNotBound'] = true;
                }
                
                break; 
            }
        }

        return $values;
    }

    private function extractValuesFromDefinition(string $pattern): array{
        
        preg_match_all("/$pattern/", $this->definition, $values, PREG_SET_ORDER);
        
            return $values;

    }


}