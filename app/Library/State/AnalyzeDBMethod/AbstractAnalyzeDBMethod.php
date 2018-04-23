<?php

namespace App\Library\State\AnalyzeDBMethod;

use App\Model\ChangeRequestInput;
use App\Library\Database\Database;
use App\Library\CustomModel\DBTargetConnection;

abstract class AbstractAnalyzeDBMethod
{
    /**
     * Undocumented variable
     *
     * @var ChangeRequestInput
     */
    private $changeRequestInput = null ;
    /**
     * Undocumented variable
     *
     * @var Database
     */
    private $database = null;
    
    private function isUnique() : boo
    {
        \strcasecmp($this->changeRequestInput->unique, 'N') == 0 ? false : true ;
    }
    
    abstract public function analyze(): bool;
    abstract public function modify(DBTargetConnection $dbTargetConnection): bool;
}
