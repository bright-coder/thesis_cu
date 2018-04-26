<?php

namespace App\Library\State\AnalyzeDBMethod;

use App\Model\ChangeRequestInput;
use App\Library\Database\Database;
use App\Library\CustomModel\DBTargetInterface;

abstract class AbstractAnalyzeDBMethod
{
    /**
     * Undocumented variable
     *
     * @var ChangeRequestInput
     */
    protected $changeRequestInput = null ;
    /**
     * Undocumented variable
     *
     * @var Database
     */
    protected $database = null;

    /**
     * Undocumented variable
     *
     * @var boolean
     */
    protected $instanceImpact = false;
    
    /**
     * Undocumented variable
     *
     * @var boolean
     */
    protected $schemaImpact = false;

    /**
     * Undocumented variable
     *
     * @var DBTargetInterface
     */
    protected $dbTargetConnection = null;

    /**
     * @var array
     */
    protected $instanceImpactResult = [];
    protected $schemaImpactResult = [];
    
    private function isUnique() : boo
    {
        \strcasecmp($this->changeRequestInput->unique, 'N') == 0 ? false : true ;
    }

    public function isSchemaImpact(): bool { return $this->schemaImpact; }
    public function isInstanceImpact() : bool {
        return count($this->instanceImpactResult) > 0; 
    }

    public function getInstanceImpactResult(): array {
        $this->instanceImpactResult = array_unique($this->instanceImpactResult, SORT_REGULAR); 
        return $this->instanceImpactResult;
    }
    
    public function getSchemaImpactResult(): array {
        return $this->schemaImpactResult;
    }

    abstract public function analyze(): bool;
    abstract public function modify(): bool;
    
    
}
