<?php

namespace App\Library\Constraint;

use App\Library\Constraint\Constraint;

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

        $this->extractMaxMin();
    }

    public function getName(): string{
        return $this->name;
    }

    public function getType(): string{
        return Constraint::CHECK;
    }

    public function getColumns(): array{
        return $this->checkColumns;
    }

    public function getDetail(): array{
        return ['definition' => $definition,'min' => $min, '$max' => $max];
    }

    private function extractMaxMin(): void{
        $tempDefinition = str_split($this->definition);
        $count = ['(' => 0 , '[' => 0 , ']' => 0, ')' => 0];
        while(!empty($tempDefinition)){
            $char = array_shift($tempDefinition);
            if($char == '('){
                $count['(']++;
            }
            elseif($char == ")"){
                $count[')']++;
            }
            elseif($char ==)
            break;
        }
    }

    private function extractFromOperator(string $column): string{ 
        return \strpos($this->definition,'['.$column.']');
    }

}