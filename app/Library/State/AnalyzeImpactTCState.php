<?php

namespace App\Library\State;

use App\Library\State\StateInterface;
use App\Library\State\AnalyzeImpactRTMState;
use App\Model\ChangeRequest;
use App\Model\ChangeRequestInput;
use App\Model\TestCase;
use App\Model\TestCaseInput;
use App\Model\RequirementTraceabilityMatrix;
use App\Model\RequirementTraceabilityMatrixRelation;
use App\Model\Project;
use DB;
use App\Library\ChangeAnalysis;
use App\Library\CustomModel\DBTargetConnection;
use App\Library\CustomModel\DBTargetInterface;

class AnalyzeImpactTCState implements StateInterface
{
    private $tcImpactResult = [];
    
    /**
     * Undocumented variable
     *
     * @var DBTargetInterface
     */
    private $dbTargetConnection = null;

    public function getStateName(): String
    {
        return 'AnalyzeImpactTCState';
    }

    public function analyze(ChangeAnalysis $changeAnalysis): void
    {
        $projectId = $changeAnalysis->getProjectId();
        $rtmId = RequirementTraceabilityMatrix::where('projectId', $projectId)->first()->id;

        $tcNoList = [];
        $tcAll = TestCase::where('projectId', $projectId)->get();
        foreach ($tcAll as $tc) {
            $tcNoList[] = intval(explode('-', $tc->no)[2]);
        }
        sort($tcNoList);

        foreach ($changeAnalysis->getFrImpactResult() as $frImpact) {
            $tcList = RequirementTraceabilityMatrixRelation::where([
                ['requirementTraceabilityMatrixId', $rtmId],
                ['functionalRequirementId', $frImpact['id']],
                ['activeFlag', 'Y']
            ])->get();
            
            $isCreateNewTc = false;
            foreach ($frImpact['inputList'] as $frInput) {
                if ($frInput['changeType'] == 'add'|| $frInput['changeType'] == 'delete') {
                    $isCreateNewTc = true;
                    break;
                }
            }

            if ($isCreateNewTc) {
                foreach ($tcList as $oldTc) {

                    // delete oldTc
                    $this->addTcImpactResult($frImpact['id'], null, 'delete', $oldTc->testCaseId);
                    $prefix = Project::where('id', $projectId)->first()->prefix;
                    // $tcNew = new TestCase;
                    // $tcNew->projectId = $changeAnalysis->projectId;
                    // $tcNew->no = $prefix."-TC-".($tcNoList[count($tcNoList)-1]+1);
                    // $tcNew->type = $oldTc->type;
                    // $tcNew->activeFlag = 'Y';
                    // $tcNew->save();
                    $tcNoList[] = $tcNoList[count($tcNoList)-1]+1;
                    $tcNewNo = $prefix."-TC-".$tcNoList[count($tcNoList)-1];

                    $tcInputChangeList = [];
                    foreach ($frImpact['inputList'] as $frInput) {
                        if ($frInput['changeType'] == 'edit') {
                            $oldValue = $this->findOldValue($oldTc->testCaseId, $frInput['old']['name']);
                            $newValue = $this->findNewValue(
                                $oldValue,
                                $frInput['old']['tableName'],
                                $frInput['old']['columnName'],
                                $changeAnalysis->getDBImpactResult()

                            );
                            if ($newValue !== null) {
                                $tcInputChangeList[] = [
                                'inputName' => $frInput['old']['name'],
                                'old' => $oldValue,
                                'new' => $newValue,

                                ];
                            }
                        }
                    }
                    $this->addTcImpactResult($frImpact['id'], $tcNewNo, 'add', $oldTc->testCaseId, $tcInputChangeList);
                }
            } else {
                foreach ($tcList as $oldTc) {
                    $tcInputChangeList = [];
                    foreach ($frImpact['inputList'] as $frInput) {
                        if ($frInput['changeType'] == 'edit') {
                            $oldValue = $this->findOldValue($oldTc->testCaseId, $frInput['old']['name']);
                            if (!empty($oldValue)) {
                                $newValue = $this->findNewValue(
                                $oldValue,
                                $frInput['old']['tableName'],
                                $frInput['old']['columnName'],
                                $changeAnalysis->getDBImpactResult()

                            );
                                if ($newValue !== null) {
                                    $tcInputChangeList[] = [
                                'inputName' => $frInput['old']['name'],
                                'old' => $oldValue,
                                'new' => $newValue,

                                ];
                                }
                            }
                        }
                    }
                    if(!empty($tcInputChangeList)) {
                        $this->addTcImpactResult($frImpact['id'], null, 'edit', $oldTc->testCaseId, $tcInputChangeList);
                    }
                    
                }
            }
        }
        $this->modify($changeAnalysis->getFrImpactResult(), $projectId);
        $changeAnalysis->setTcImpactResult($this->tcImpactResult);
        //dd($changeAnalysis->getTcImpactResult());
        $changeAnalysis->setState(new AnalyzeImpactRTMState);
        $changeAnalysis->analyze();
    }

    private function findOldValue(string $tcId, string $inputName): string
    {
        $data = TestCaseInput::where([
            ['testCaseId', $tcId],
            ['name', $inputName]
        ])->first();
        return $data ? trim($data->testData) : "";
    }

    private function findNewValue(string $oldValue, string $tableName, string $columnName, array $dbImpactResult)
    {
        foreach ($dbImpactResult as $dbImpact) {
            //dd($dbImpact['schema']['tableName']);
            foreach ($dbImpact['schema'] as $index => $schema) {
                if ($schema['tableName'] == $tableName && $schema['columnName'] == $columnName) {
                    if (!empty($dbImpact['instance'][$index])) {
                        //dd($dbImpact['instance'][$index]);
                        foreach ($dbImpact['instance'][$index]['oldInstance'] as $insIndex => $instance) {
                            //dd($oldValue);
                            if ($instance[$columnName] == $oldValue) {
                                //dd($instance);
                                return $dbImpact['instance'][$index]['newInstance'][$insIndex];
                            }
                        }
                    } else {
                        return null;
                    }
                }
            }
        }
        return null;
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

    private function modify(array $frImpactResult, $projectId): void
    {
        foreach ($this->tcImpactResult as $tcImpact) {
            if ($tcImpact['changeType'] == 'add') {
                $tcNew = new TestCase;
                $tcNew->projectId = $projectId;
                $tcNew->no = $tcImpact['newNo'];
                $tcNew->type = TestCase::where('id', $tcImpact['oldTcId'])->first()->type;
                $tcNew->activeFlag = 'Y';
                $tcNew->save();
                    
                $tcOldInputList = TestCaseInput::Where('testCaseId', $tcImpact['oldTcId'])->get();
                
                foreach ($tcOldInputList as $tcOldInput) {
                    $tcInputNew = new TestCaseInput;
                    $tcInputNew->testCaseId = $tcNew->id;
                    $tcInputNew->name = $tcOldInput->name;
                    $tcInputNew->testData = trim($tcOldInput->testData);
                    $tcInputNew->save();
                }

                foreach ($tcImpact['tcInputEdit'] as $tcChangeDataInput) {
                    //dd('helloWorld');
                    TestCaseInput::where([
                        ['testCaseId', $tcNew->id],
                        ['name', $tcChangeDataInput['inputName']],
                        ['testData', trim($tcChangeDataInput['old'])]
                    ])->update(['testData' => $tcChangeDataInput['new']]);
                }

                foreach ($frImpactResult as $frImpact) {
                    if ($frImpact['id'] == $tcImpact['functionalRequirementId']) {
                        foreach ($frImpact['inputList'] as $frInput) {
                            if ($frInput['changeType'] == 'add') {
                                if ($this->connectTargetDB($projectId)) {
                                    
                                    $instanceList = $this->dbTargetConnection->getInstanceByTableName($frInput['new']['tableName']);
                                    $numRows = count($instanceList);
                                    $pickId = rand(1, $numRows) -1 ;
                                    $pickInstance = $instanceList[$pickId][$frInput['new']['columnName']];
                                } else {
                                    $pickInstance = '#ERROR';
                                }
                                $tcInputNew = new TestCaseInput;
                                $tcInputNew->testCaseId = $tcNew->id;
                                $tcInputNew->name = $frInput['new']['name'];
                                $tcInputNew->testData = $pickInstance;
                                $tcInputNew->save();
                            } elseif ($frInput['changeType'] == 'delete') {
                                TestCaseInput::where([
                                    ['testCaseId', $tcNew->id],
                                    ['name', $frInput['old']['name']]
                                ])->delete();
                            }
                        }
                    }
                }
            } elseif ($tcImpact['changeType'] == 'edit') {
                $tcOldInputList = TestCaseInput::Where('testCaseId', $tcImpact['oldTcId'])->get();

                foreach ($tcOldInputList as $tcOldInput) {
                    foreach ($tcImpact['tcInputEdit'] as $tcChangeDataInput) {
                        TestCaseInput::where([
                            ['testCaseId', $tcImpact['oldTcId']],
                            ['name', $tcChangeDataInput['inputName']],
                            ['testData', trim($tcChangeDataInput['old'])]
                        ])->update(['testData' => $tcChangeDataInput['new']]);
                    }
                }
            } elseif ($tcImpact['changeType'] == 'delete') {
                $tcOld = TestCase::find($tcImpact['oldTcId']);
                $tcOld->activeFlag = 'N';
                $tcOld->save();
            }
        }
    }


    private function addTcImpactResult(string $frId, string $tcNewNo = null, string $changeType, string $oldTcId = null, array $tcInputEdit = []): void
    {
        $this->tcImpactResult[] = [
            'functionalRequirementId' => $frId,
            'changeType' => $changeType,
            'oldTcId' => $oldTcId,
            'tcInputEdit' => $tcInputEdit
        ];
        // if ($tcInputEdit != null) {
        //     $this->tcImpactResult[count($this->tcImpactResult)-1]['tcInput'] = $tcInputEdit;
        // }
        if ($tcNewNo != null) {
            $this->tcImpactResult[count($this->tcImpactResult)-1]['newNo'] = $tcNewNo;
        }
    }
}
