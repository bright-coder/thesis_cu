<?php

namespace App\Library\Constraint;

use App\Library\Constraint\Constraint;

final class ConstraintFactory{

    public static function create(array $detail): Constraint{
        switch ($detail['type']) {
            case Constraint::PRIMARY_KEY :
                # code...
                break;
            case Constraint::FOREIGN_KEY :
                # code...
                break;
            case Constraint::UNIQUE :
                # code...
                break;
            case Constraint::CHECK :
                # code...
                break;
            default:
                # code...
                break;
        }
    }
}