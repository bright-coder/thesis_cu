<?php

namespace App\Library\Constraint;

use App\Library\Constraint\Constraint;
use App\Library\Constraint\PrimaryKey;
use App\Library\Constraint\ForeignKey;
use App\Library\Constraint\Unique;
use App\Library\Constraint\Check;

final class ConstraintFactory{

    public static function create(array $detail): Constraint{
        switch ($detail['type']) {
            case Constraint::PRIMARY_KEY :
                return new PrimaryKey($detail['name'],$detail['columnName']);
            case Constraint::FOREIGN_KEY :
                return new ForeignKey($detail['name'], ['table' => $detail['fromTable'], 'column' => $detail['fromColumn'] ], ['table' => $detail['toTable'], 'column' => $detail['toColumn'] ]);
            case Constraint::UNIQUE :
                return new Unique($detail['name'],$detail['columnName']);
            case Constraint::CHECK :
                return new Check($detail['name'],$detail['columnName'],$detail['definition']);
        }
    }
}