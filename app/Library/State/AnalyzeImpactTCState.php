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
        $rtmId = RequirementTraceabilityMatrix::where('projectId', $projectId)->id;
        foreach ($changeAnalysis->getFrImpactResult() as $frImpact) {
            $tcList = RequirementTraceabilityMatrixRelation::where([
                ['requirementTraceabilityMatrixId', $rtmId],
                ['functionalRequirementId', $frImpact['id']]
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
                    $this->addTcImpactResult($frImpact['id'], $oldTc->id, 'delete');
                    
                    $tcNew = new TestCase;
                    $tcNew->projectId = $changeAnalysis->projectId;
                    $tcNew->no = "#####";
                    $tcNew->type = $oldTc->type;
                    $tcNew->activeFlag = 'Y';
                    $tcNew->save();

                    $tcInputChangeList = [];
                    foreach ($frImpact['inputList'] as $frInput) {
                        if ($frInput['changeType'] == 'edit') {
                            $oldValue = $this->findOldValue($oldTc->id, $frInput['old']['name']);
                            $newValue = $this->findNewValue(
                                $oldValue,
                                $frInput['old']['tableName'],
                                $frInput['old']['columnName'],
                                $changeAnalysis->getDBImpactResult()

                            );
                            if($newValue !== null) {
                                $tcInputChangeList[] = [
                                'inputName' => $frInput['old']['name'],
                                'old' => $oldValue,
                                'new' => $newValue,

                                ];
                            }
                        }
                    }
                    $this->addTcImpactResult($frImpact['id'], $tcNew->id, 'add', $oldTc->id, $tcInputChangeList);
                }
            } else {
                foreach ($tcList as $oldTc) {
                    $tcInputChangeList = [];
                    foreach ($frImpact['inputList'] as $frInput) {
                        if ($frInput['changeType'] == 'edit') {
                            $oldValue = $this->findOldValue($oldTc->id, $frInput['old']['name']);
                            $newValue = $this->findNewValue(
                                $oldValue,
                                $frInput['old']['tableName'],
                                $frInput['old']['columnName'],
                                $changeAnalysis->getDBImpactResult()

                            );
                            if($newValue !== null) {
                                $tcInputChangeList[] = [
                                'inputName' => $frInput['old']['name'],
                                'old' => $oldValue,
                                'new' => $newValue,

                                ];
                            }
                            
                        }
                    }
                    $this->addTcImpactResult($frImpact['id'], $oldTc->id, 'edit', null, $tcInputChangeList);
                }
            }
        }
        $this->modify();
        $changeAnalysis->setTcImpactResult($this->tcImpactResult);
        $changeAnalysis->setState(new AnalyzeImpactRTMState);
        $changeAnalysis->analyze();
    }

    private function findOldValue(string $tcId, string $inputName): string
    {
        return TestCaseInput::where([
            ['testCaseId', $tcId],
            ['name', $inputName]
        ])->first()->testData;
    }

    private function findNewValue(string $oldValue, string $tableName, string $columnName, array $dbImpactResult)
    {
        foreach ($dbImpactResult as $dbImpact) {
            if ($dbImpact['schema']['tableName'] == $tableName && $dbImpact['schema']['columnName'] == $columnName) {
                if (count($dbImpact['instance'] > 0)) {
                    foreach ($dbImpact['instance']['oldInstance'] as $index => $instance) {
                        if ($instance[$columnName] == $oldValue) {
                            return $dbImpact['instance']['newInstance'][$index];
                        }
                    }
                }
                else {
                    return null;
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

    private function modify(array $frImpactResult): void
    {
        foreach ($this->tcImpactResult as $tcImpact) {
            if ($tcImpact['changeType'] == 'add') {
                $tcOldInputList = TestCaseInput::Where('testCaseId', $tcImpact['oldTcId'])->get();

                foreach ($tcOldInputList as $tcOldInput) {
                    $tcInputNew = new TestCaseInput;
                    $tcInputNew->testCaseId = $tcImpact['id'];
                    $tcInputNew->name = $tcOldInput->name;
                    $tcInputNew->data = $tcOldInput->data;
                    $tcInputNew->save();
                }

                foreach($tcImpact['tcInput'] as $tcChangeDataInput) {
                    TestCaseInput::where([
                        ['testCaseId', $tcImpact['id']],
                        ['name', $tcChangeDataInput['inputName']],
                        ['testData', $tcChangeDataInput['old']] 
                    ])->update(['testData', $tcChangeDataInput['new']]);
                }

                foreach ($frImpactResult as $frImpact) {
                    if ($frImpact['id'] == $tcImpact['functionalRequirementId']) {
                        foreach ($frImpact['inputList'] as $frInput) {
                            if ($frInput['changeType'] == 'add') {
                                if($this->connectTargetDB()) {
                                    $instaceList = $this->dbTargetConnection->getInstanceByTableName($frInput['tableName']);
                                    $numRows = count($instanceList);
                                    $pickId = rand(1, $numRows) -1 ;
                                    $pickInstance = $instaceList[$pickId][$frInput['columnName']];
                                }
                                else {
                                    $pickInstance = '#ERROR';
                                }
                                $tcInputNew = new TestCaseInput;
                                $tcInputNew->testCaseId = $tcImpact->id;
                                $tcInputNew->name = $frInput['new']['name'];
                                $tcInputNew->data = $pickInstance;
                                $tcInputNew->save();
                            }
                            elseif($frInput['changeType'] == 'delete') {
                                TestCaseInput::where([
                                    ['testCaseId', $tcImpact['id']],
                                    ['name', $frInput['old']['name']]
                                ])->delete();
                            }
                        }
                    }
                }
            }
            elseif($tcImpact['changeType'] == 'edit') {
                $tcOldInputList = TestCaseInput::Where('testCaseId', $tcImpact['oldTcId'])->get();

                foreach($tcOldInputList as $tcOldInput) {
                    foreach($tcImpact['tcInput'] as $tcChangeDataInput) {
                        TestCaseInput::where([
                            ['testCaseId', $tcImpact['id']],
                            ['name', $tcChangeDataInput['inputName']],
                            ['testData', $tcChangeDataInput['old']] 
                        ])->update(['testData', $tcChangeDataInput['new']]);
                    }
                }
            }
            elseif($tcImpact['changeType'] == 'delete') {
                $tcOld = TestCase::find($tcImpact['id']);
                $tcOld->activeFlag = 'N';
                $tcOld->save();
            }
        }
    }


    private function addTcImpactResult(string $frId, string $tcId, string $changeType, string $oldTcId = null, array $tcInputEdit = []): void
    {
        $tcNo = TestCase::where('id', $tcId)->first()->no;
        $this->tcImpactResult[] = [
            'functionalRequirementId' => $frId,
            'id' => $tcId,
            'no' => $tcNo,
            'changeType' => $changeType,
            'oldTcId' => $oldTcId
        ];
        if (count($tcInputEdit) > 0) {
            $this->tcImpactResult[count($this->tcImpactResult)-1]['tcInput'] = $tcInputEdit;
        }
    }
}
