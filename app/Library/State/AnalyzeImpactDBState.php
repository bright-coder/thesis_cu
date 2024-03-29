<?php

namespace App\Library\State;

use App\Library\Builder\DatabaseBuilder;
use App\Library\CustomModel\DBTargetConnection;
use App\Library\CustomModel\DBTargetInterface;
use App\Library\State\StateInterface;
use App\Library\State\AnalyzeImpactFRState;
use App\Library\State\AnalyzeDBMethod\AnalyzeDBAdd;
use App\Library\State\AnalyzeDBMethod\AnalyzeDBEdit;
use App\Library\State\AnalyzeDBMethod\AnalyzeDBDel;

use App\Model\Project;
use App\Model\FunctionalRequirement;
use App\Model\FunctionalRequirementInput;
use App\Model\ChangeRequest;
use App\Model\ChangeRequestInput;
use App\Library\ChangeAnalysis;
use DB;
use App\Library\Database\Database;

class AnalyzeImpactDBState implements StateInterface
{
    /**
     * Undocumented variable
     *
     * @var DBTargetInterface
     */
    private $dbTargetConnection = null;
    /**
     * Undocumented variable
     *
     * @var Database
     */
    private $dbTarget = null;
    private $errorMessage = '';

    public function __construct()
    {
    }

    public function getStateName(): String
    {
        return 'AnalyzeImpactDBState';
    }

    public function analyze(ChangeAnalysis $changeAnalysis) : void
    {
        $cr = $changeAnalysis->getChangeRequest();
        $projectId = $changeAnalysis->getProjectId();
        if ($this->connectTargetDB($projectId)) {
            $this->getDbSchema();
            if (!$this->validateChangeRequestInput($changeAnalysis->getAllChangeRequestInput())) {
                $cr->status = 0;
                $cr->save();
                return;
            }
            
            foreach ($changeAnalysis->getAllChangeRequestInput() as $changeRequestInput) {
                switch ($changeRequestInput->changeType) {
                    case 'add':
                        $analyzer = new AnalyzeDBAdd($this->dbTarget, $changeRequestInput, $this->dbTargetConnection);
                        break;
                    case 'edit':
                        $analyzer = new AnalyzeDBEdit($this->dbTarget, $changeRequestInput, $this->dbTargetConnection);
                        break;
                    case 'delete':
                        $analyzer = new AnalyzeDBDel($this->dbTarget, $changeRequestInput, $this->dbTargetConnection);
                        break;
                    default:
                        # code...
                        break;

                }

            
                $result = $analyzer->analyze();
                //$analyzer->modify();
                if ($result) {
                    
                    foreach ($result['tableList'] as $tableName => $columnList) {
                        foreach ($columnList as $columnName => $info) {
                            
                            $changeAnalysis->addSchemaImpactResult($tableName, $columnName, $info['changeType'], $info['old'], $info['new'], $info['isPK']);
                            if($info['instance']) {
                                $changeAnalysis->addInstanceResult($tableName, $columnName, 
                                    $info['instance']['pkRecords'], 
                                    $info['changeType'], 
                                    $info['instance']['oldValues'], 
                                    $info['instance']['newValues']
                                );
                            }
                        }
                        foreach ($result['cckDelete'] as $cck) {
                            $changeAnalysis->addKeyConstaintImpactResult($tableName, $cck['info']->getName(), 'UNIQUE', $cck['info']->getColumns());
                        }
                        foreach ($result['fkDelete'] as $fk) {
                            $changeAnalysis->addKeyConstaintImpactResult($tableName, $fk['info']->getName(), 'FK', $fk['info']->getColumns());
                        }
                    }
                }
            }
            $this->dbTargetConnection->updateDatabase(
                $changeAnalysis->getSchemaImpactResult(),
                $changeAnalysis->getInstanceImpactResult(),
                $changeAnalysis->getKeyConstraintImpactResult(),
                $this->dbTarget
            );
        
            //dd($changeAnalysis->getSchemaImpactResult());
            //dd($changeAnalysis->getInstanceImpactResult()['Rent_User']);
            //dd($changeAnalysis->getKeyConstraintImpactResult());

            $cr->status = 1;
            $cr->save();
            $changeAnalysis->saveSchemaImpact();
            $changeAnalysis->saveInstanceImpact();
            $changeAnalysis->saveKeyConstraintImpact();

            $changeAnalysis->setState(new AnalyzeImpactFRState);
            $changeAnalysis->analyze();
        }
    }


    private function validateChangeRequestInput(array $changeRequestInputList) : bool
    {
        $result = true;
        foreach ($changeRequestInputList as $changeRequestInput) {
            $changeRequestInput->status = 1;
            if ($changeRequestInput->changeType == 'edit') {
                $frInput = FunctionalRequirementInput::where('id', $changeRequestInput->frInputId)->first();
                $table = $this->dbTarget->getTableByName($frInput->tableName);
                
                if ($table->isPK($frInput->columnName)) {
                    if ($changeRequestInput->unique != null) {
                        $changeRequestInput->status = 0;
                        $changeRequestInput->errorMessage = 'Cannot change Unique at Primary key column.';
                    } elseif ($changeRequestInput->nullable != null) {
                        $changeRequestInput->status = 0;
                        $changeRequestInput->errorMessage = 'Cannot change Nullable at Primary key column.';
                    }
                } elseif ($table->isFK($frInput->columnName)) {
                    // if ($changeRequestInput->default != null) {
                    //     $changeRequestInput->status = 0;
                    //     $changeRequestInput->errorMessage = 'Cannot change Default at Foreign Key column.';
                    // } else
                    if ($changeRequestInput->min != null) {
                        $changeRequestInput->status = 0;
                        $changeRequestInput->errorMessage = 'Cannot change Min at Foreign Key column.';
                    } elseif ($changeRequestInput->max != null) {
                        $changeRequestInput->status = 0;
                        $changeRequestInput->errorMessage = 'Cannot change Max at Foreign Key column.';
                    } elseif ($changeRequestInput->unique != null) {
                        if (\strcasecmp($changeRequestInput->unique, 'Y') == 0) {
                            $duplicateInstance = $this->dbTarget->getDuplicateInstance($table->getName(), [$frInput->columnName]);
                            if (count($duplicateInstance > 0)) {
                                // cannot modify impact; Referential Integrity;
                                $changeRequestInput->status = 0;
                                $changeRequestInput->errorMessage = 'Conflict with Referential Integrity Constraint.';
                            }
                        }
                    } elseif ($changeRequestInput->nullable != null) {
                        if (\strcasecmp($changeRequestInput->nullable, 'N') == 0) {
                            $nullInstance =  $this->dbTarget->getInstanceByTableName($table->getName(), "{$frInput->columnName} IS NULL");
                            if (count($nullInstance) > 0) {
                                $changeRequestInput->status = 0;
                                $changeRequestInput->errorMessage = 'Conflict with Referential Integrity Constraint.';
                            }
                        }
                    }
                }
            } elseif ($changeRequestInput->changeType == 'delete') {
                $frInput = FunctionalRequirementInput::where('id', $changeRequestInput->frInputId)->first();
                $fr = FunctionalRequirement::where('id', $frInput->frId)->first();
                $project = Project::where('id', $fr->projectId)->first();
                $functionalRequirements = FunctionalRequirement::where('projectId', $project->id)->get();
                $table = $this->dbTarget->getTableByName($frInput->tableName);
                
                $foundOther = false;
                foreach ($functionalRequirements as $frAll) {
                    if ($fr->id != $frAll->id) {
                        $frInputs = FunctionalRequirementInput::where([
                        ['frId',$fr->id],
                        ['name', $changeRequestInput->name]
                        ])->first();
        
                        if ($frInputs) {
                            $foundOther = true;
                            break;
                        }
                    }
                }

                if ($table->isPK($frInput->columnName) && $foundOther == false) {
                    $changeRequestInput->status = 0;
                    $changeRequestInput->errorMessage = 'Cannot delete Primary key column.';
                }
            }
            if ($changeRequestInput->status == 0) {
                $result = false;
            }
            $changeRequestInput->save();
        }

        return $result;
    }

    private function connectTargetDB(string $projectId): bool
    {
        $project = Project::where('id', $projectId)->first();
        $this->dbTargetConnection = DBTargetConnection::getInstance(
            $project->dbType,
            $project->dbServer,
            $project->dbPort,
            $project->dbName,
            $project->dbUsername,
            $project->dbPassword
        );

        if (!$this->dbTargetConnection->connect()) {
            return false;
        }

        return true;
    }

    private function getDbSchema(): void
    {
        $databaseBuilder = new DatabaseBuilder($this->dbTargetConnection);
        $databaseBuilder->setUpTablesAndColumns();
        $this->dbTarget = $databaseBuilder->getDatabase();
    }
}
