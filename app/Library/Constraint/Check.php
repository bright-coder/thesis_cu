<?php

namespace App\Library\Constraint;

use App\Library\Constraint\Constraint;

class Check implements Constraint{
    
    private $name;
    private $checkColumns;
    private $definition;
    private $max;
    private $min;

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

    public function getDetail(): array{
        return ['definition' => $definition,'min' => $min, '$max' => $max];
    }

    private function extractMaxMin(): void{
        // do something
    }

}