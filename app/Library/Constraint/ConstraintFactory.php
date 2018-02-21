<?php

namespace App\Library\Constraint;

use App\Library\Constraint\Constraint;
use App\Library\Constraint\ConstraintType;
use App\Library\Constraint\PrimaryKey;
use App\Library\Constraint\ForeignKey;
use App\Library\Constraint\Unique;
use App\Library\Constraint\Check;

final class ConstraintFactory{

    public static function create(array $detail): Constraint{
        switch ($detail['type']) {
            case ConstraintType::PRIMARY_KEY :
                return new PrimaryKey($detail['name'],$detail['columnName']);
            case ConstraintType::FOREIGN_KEY :
                return new ForeignKey($detail['name'],$detail['links']);
            case ConstraintType::UNIQUE :
                return new Unique($detail['name'],$detail['columnName']);
            case ConstraintType::CHECK :
                return new Check($detail['name'],$detail['columnName'],$detail['definition']);
        }
    }
}