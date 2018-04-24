<?php

namespace App\Library\Constraint;

use App\Library\Constraint\Constraint;
use App\Library\Constraint\ConstraintType;

class ForeignKey implements Constraint
{
    private $name;
    private $links;

    public function __construct(string $name = "", array $links)
    {
        $this->name = $name;
        $this->links = $links;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return ConstraintType::FOREIGN_KEY;
    }

    public function getColumns(): array
    {
        /**
         * 
         *           -links: array:1 [
         *   0 => array:2 [
         *      "from" => array:2 [
         *       "tableName" => "APPOINTMENT"
         *       "columnName" => "mdId"
         *     ]
         *     "to" => array:2 [
         *       "tableName" => "DOCTOR"
         *       "columnName" => "mdId"
         *     ]
         *   ]
         * ]
         * 
         */
        return $this->links;
    }

    public function getDetail(): array
    {
        return [];
    }

}
