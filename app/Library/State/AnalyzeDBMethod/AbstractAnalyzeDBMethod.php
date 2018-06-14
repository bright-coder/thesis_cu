<?php

namespace App\Library\State\AnalyzeDBMethod;

use App\Model\ChangeRequestInput;
use App\Library\Database\Database;
use App\Library\CustomModel\DBTargetInterface;
use App\Model\FunctionalRequirementInput;
use App\Library\Constraint\Constraint;

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
     * @var DBTargetInterface
     */
    protected $dbTargetConnection = null;

    /**
    * @var FunctionalRequirementInput;
    */
    protected $functionalRequirementInput = null;

    protected function getFRInputById(string $frId) : FunctionalRequirementInput
    {
        return FunctionalRequirementInput::where('id',$frId)->first();
    }

    abstract public function analyze(): array;
}
